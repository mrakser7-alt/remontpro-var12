<?php
require_once __DIR__ . '/../includes/config.php';

$user = current_user();
if (!$user) {
    redirect('login.php?redirect=admin/requests.php');
}
if (!in_array($user['role'], ['admin', 'manager'], true)) {
    set_flash('error', 'Недостаточно прав для доступа к заявкам.');
    redirect('cabinet.php');
}
$isAdmin = $user['role'] === 'admin';

$statuses = ['new', 'measurement', 'in_progress', 'completed', 'cancelled'];

// ---------- Обработка действий ----------
if (is_post() && verify_csrf()) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'update_request' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $status = (string)($_POST['status'] ?? 'new');
        $comment = trim((string)($_POST['manager_comment'] ?? ''));
        if (!in_array($status, $statuses, true)) {
            set_flash('error', 'Недопустимый статус заявки.');
        } elseif (mb_strlen($comment, 'UTF-8') > 1000) {
            set_flash('error', 'Комментарий слишком длинный (максимум 1000 символов).');
        } else {
            $stmt = db()->prepare('UPDATE requests SET status = ?, manager_comment = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$status, $comment, $id]);
            cache_clear();
            set_flash('success', 'Заявка №' . $id . ' обновлена. Статус: ' . status_badge($status)['label'] . '.');
        }
        redirect('admin/requests.php?id=' . $id);
    }

    if ($action === 'delete_request' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if (!$isAdmin) {
            set_flash('error', 'Удалять заявки может только администратор.');
        } else {
            $stmt = db()->prepare('DELETE FROM requests WHERE id = ?');
            $stmt->execute([$id]);
            cache_clear();
            set_flash('success', 'Заявка №' . $id . ' удалена.');
        }
        redirect('admin/requests.php');
    }
}

// ---------- Детальный просмотр ----------
$viewId = (int)($_GET['id'] ?? 0);
$viewRequest = null;
if ($viewId) {
    $stmt = db()->prepare('SELECT r.*, u.name AS account_name, u.email AS account_email FROM requests r LEFT JOIN users u ON u.id = r.user_id WHERE r.id = ?');
    $stmt->execute([$viewId]);
    $viewRequest = $stmt->fetch() ?: null;
    if (!$viewRequest) {
        set_flash('error', 'Заявка не найдена.');
        redirect('admin/requests.php');
    }
}

// ---------- Список ----------
$filterStatus = (string)($_GET['status'] ?? '');
if ($filterStatus !== '' && !in_array($filterStatus, $statuses, true)) {
    $filterStatus = '';
}
$search = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = '1=1';
$params = [];
if ($filterStatus !== '') {
    $where .= ' AND r.status = ?';
    $params[] = $filterStatus;
}
if ($search !== '') {
    $where .= ' AND (r.name LIKE ? OR r.phone LIKE ? OR r.email LIKE ? OR r.repair_type LIKE ? OR r.address LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like);
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM requests r WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare("SELECT r.* FROM requests r WHERE $where ORDER BY CASE r.status WHEN 'new' THEN 0 WHEN 'measurement' THEN 1 WHEN 'in_progress' THEN 2 ELSE 3 END, r.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$requests = $stmt->fetchAll();

$statusCounts = array_fill_keys($statuses, 0);
$statusCounts['all'] = 0;
foreach (db()->query('SELECT status, COUNT(*) cnt FROM requests GROUP BY status')->fetchAll() as $row) {
    if (isset($statusCounts[$row['status']])) {
        $statusCounts[$row['status']] = (int)$row['cnt'];
    }
    $statusCounts['all'] += (int)$row['cnt'];
}

$adminTitle = $viewRequest ? 'Заявка №' . (int)$viewRequest['id'] : 'Заявки на ремонт';
$adminBreadcrumb = 'Главная / Заявки';
$activeAdmin = 'requests';
require __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($viewRequest): ?>
    <?php $badge = status_badge($viewRequest['status']); ?>
    <div class="flex-between mb-3 flex-wrap">
        <a class="link-arrow" href="<?= url('admin/requests.php') ?>">← Назад к списку</a>
        <span class="badge <?= e($badge['class']) ?>"><?= e($badge['label']) ?></span>
    </div>

    <div class="admin-grid">
        <div class="panel">
            <div class="panel__head"><div><h2>Информация о заявке</h2><p>Создана <?= e(format_date($viewRequest['created_at'])) ?> · обновлена <?= e(format_date($viewRequest['updated_at'])) ?></p></div></div>
            <div class="panel__body">
                <div class="form-grid">
                    <div><div class="stat-card__label">Клиент</div><strong><?= e($viewRequest['name']) ?></strong><?php if ($viewRequest['account_name']): ?><div class="cell-sub">Аккаунт: <?= e($viewRequest['account_name']) ?></div><?php endif; ?></div>
                    <div><div class="stat-card__label">Телефон</div><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $viewRequest['phone'])) ?>"><strong><?= e($viewRequest['phone']) ?></strong></a></div>
                    <div><div class="stat-card__label">E-mail</div><strong><?= e($viewRequest['email'] ?: '—') ?></strong></div>
                    <div><div class="stat-card__label">Тип ремонта</div><strong><?= e($viewRequest['repair_type'] ?: '—') ?></strong></div>
                    <div><div class="stat-card__label">Площадь</div><strong><?= $viewRequest['area'] ? (int)$viewRequest['area'] . ' м²' : '—' ?></strong></div>
                    <div><div class="stat-card__label">Бюджет</div><strong><?= $viewRequest['budget'] ? format_price((int)$viewRequest['budget']) : '—' ?></strong></div>
                    <div class="form-row--full"><div class="stat-card__label">Адрес объекта</div><strong><?= e($viewRequest['address'] ?: '—') ?></strong></div>
                    <div class="form-row--full"><div class="stat-card__label">Сообщение клиента</div><p style="margin:4px 0 0"><?= $viewRequest['message'] ? nl2br(e($viewRequest['message'])) : '—' ?></p></div>
                </div>

                <?php if ($viewRequest['status'] !== 'cancelled'): ?>
                    <?php
                    $steps = [['new', 'Принята'], ['measurement', 'Замер и смета'], ['in_progress', 'В работе'], ['completed', 'Завершена']];
                    $currentIndex = array_search($viewRequest['status'], array_column($steps, 0), true);
                    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
                    ?>
                    <div class="status-steps">
                        <?php foreach ($steps as $i => $step): ?>
                            <div class="status-step<?= $i <= $currentIndex ? ' is-done' : '' ?>"><?= e($step[1]) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="cabinet-main">
            <div class="panel">
                <div class="panel__head"><div><h2>Обработка заявки</h2><p>Измените статус и оставьте комментарий</p></div></div>
                <div class="panel__body">
                    <form method="post" action="<?= url('admin/requests.php?id=' . (int)$viewRequest['id']) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_request">
                        <input type="hidden" name="id" value="<?= (int)$viewRequest['id'] ?>">
                        <div class="field mb-2">
                            <label for="req-status">Статус</label>
                            <select id="req-status" name="status">
                                <?php foreach ($statuses as $s): $sb = status_badge($s); ?>
                                    <option value="<?= e($s) ?>" <?= $viewRequest['status'] === $s ? 'selected' : '' ?>><?= e($sb['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="req-comment">Комментарий менеджера</label>
                            <textarea id="req-comment" name="manager_comment" rows="5" maxlength="1000" data-counter="#req-counter" placeholder="Виден клиенту в личном кабинете"><?= e($viewRequest['manager_comment']) ?></textarea>
                            <span class="field__hint" id="req-counter">0 / 1000</span>
                        </div>
                        <button class="btn btn--primary btn--block mt-3" type="submit">Сохранить</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel__head"><h3>Быстрые действия</h3></div>
                <div class="panel__body">
                    <div style="display:grid;gap:8px">
                        <a class="btn btn--ghost btn--sm" href="tel:<?= e(preg_replace('/[^\d+]/', '', $viewRequest['phone'])) ?>">Позвонить клиенту</a>
                        <?php if ($viewRequest['email']): ?>
                            <a class="btn btn--ghost btn--sm" href="mailto:<?= e($viewRequest['email']) ?>">Написать на e-mail</a>
                        <?php endif; ?>
                        <a class="btn btn--ghost btn--sm" href="<?= url('cabinet.php') ?>">Личный кабинет</a>
                    </div>
                    <?php if ($isAdmin): ?>
                        <hr style="border:0;border-top:1px solid var(--line);margin:18px 0">
                        <form method="post" action="<?= url('admin/requests.php') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_request">
                            <input type="hidden" name="id" value="<?= (int)$viewRequest['id'] ?>">
                            <button class="btn btn--danger btn--block" type="submit" data-confirm="Удалить заявку №<?= (int)$viewRequest['id'] ?> без возможности восстановления?">Удалить заявку</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="panel mb-3">
        <div class="panel__body" style="padding:18px 22px">
            <form method="get" action="<?= url('admin/requests.php') ?>" style="display:flex;gap:10px;flex-wrap:wrap">
                <?php if ($filterStatus): ?><input type="hidden" name="status" value="<?= e($filterStatus) ?>"><?php endif; ?>
                <div class="field" style="flex:1;min-width:220px;gap:0">
                    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Поиск по имени, телефону, адресу, типу…">
                </div>
                <button class="btn btn--dark" type="submit">Найти</button>
                <?php if ($search): ?><a class="btn btn--ghost" href="<?= url('admin/requests.php' . ($filterStatus ? '?status=' . urlencode($filterStatus) : '')) ?>">Сбросить</a><?php endif; ?>
            </form>
        </div>
    </div>

    <div class="filter-bar">
        <a class="filter-chip<?= $filterStatus === '' ? ' is-active' : '' ?>" href="<?= url('admin/requests.php') ?>">Все <span><?= $statusCounts['all'] ?></span></a>
        <?php foreach ($statuses as $s): $sb = status_badge($s); ?>
            <a class="filter-chip<?= $filterStatus === $s ? ' is-active' : '' ?>" href="<?= url('admin/requests.php?status=' . $s) ?>"><?= e($sb['label']) ?> <span><?= $statusCounts[$s] ?></span></a>
        <?php endforeach; ?>
    </div>

    <div class="panel">
        <div class="panel__body panel__body--flush">
            <?php if (!$requests): ?>
                <div class="empty-state">
                    <div class="empty-state__icon">🔍</div>
                    <h3>Заявки не найдены</h3>
                    <p>Измените фильтр или поисковый запрос.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>№</th><th>Клиент</th><th>Тип ремонта</th><th>Площадь</th><th>Бюджет</th><th>Дата</th><th>Статус</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): $badge = status_badge($request['status']); ?>
                                <tr>
                                    <td>#<?= (int)$request['id'] ?></td>
                                    <td class="cell-main"><?= e($request['name']) ?><div class="cell-sub"><?= e($request['phone']) ?></div></td>
                                    <td><?= e($request['repair_type'] ?: '—') ?></td>
                                    <td><?= $request['area'] ? (int)$request['area'] . ' м²' : '—' ?></td>
                                    <td><?= $request['budget'] ? format_price((int)$request['budget']) : '—' ?></td>
                                    <td><?= e(format_date($request['created_at'])) ?></td>
                                    <td><span class="badge <?= e($badge['class']) ?>"><?= e($badge['label']) ?></span></td>
                                    <td>
                                        <div class="cell-actions">
                                            <a class="icon-btn" href="<?= url('admin/requests.php?id=' . (int)$request['id']) ?>" title="Открыть">
                                                <svg viewBox="0 0 24 24"><path d="M12 5c5 0 9 4.5 10 7-1 2.5-5 7-10 7S3 14.5 2 12c1-2.5 5-7 10-7zm0 2c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm0 2a3 3 0 110 6 3 3 0 010-6z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <?php $qs = http_build_query(array_filter(['status' => $filterStatus, 'q' => $search, 'page' => $i])); ?>
                <?php if ($i === $page): ?><span class="is-current"><?= $i ?></span><?php else: ?><a href="<?= url('admin/requests.php?' . $qs) ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
