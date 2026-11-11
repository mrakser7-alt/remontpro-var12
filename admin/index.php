<?php
require_once __DIR__ . '/../includes/config.php';

$totalRequests = (int)db()->query('SELECT COUNT(*) FROM requests')->fetchColumn();
$newRequests = (int)db()->query("SELECT COUNT(*) FROM requests WHERE status = 'new'")->fetchColumn();
$inProgress = (int)db()->query("SELECT COUNT(*) FROM requests WHERE status IN ('measurement','in_progress')")->fetchColumn();
$completed = (int)db()->query("SELECT COUNT(*) FROM requests WHERE status = 'completed'")->fetchColumn();
$totalUsers = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$pendingReviews = (int)db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
$completedBudget = (int)db()->query("SELECT COALESCE(SUM(budget),0) FROM requests WHERE status = 'completed'")->fetchColumn();
$avgBudget = (int)db()->query('SELECT COALESCE(AVG(budget),0) FROM requests WHERE budget > 0')->fetchColumn();

$latestRequests = db()->query('SELECT r.*, u.name AS user_name FROM requests r LEFT JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC LIMIT 5')->fetchAll();
$pendingList = db()->query("SELECT * FROM reviews WHERE status = 'pending' ORDER BY created_at DESC LIMIT 3")->fetchAll();
$recentUsers = db()->query('SELECT * FROM users ORDER BY created_at DESC LIMIT 4')->fetchAll();

// Заявки по месяцам за последние 6 месяцев
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime(date('Y-m-01') . " -$i months");
    $key = date('Y-m', $ts);
    $months[$key] = ['label' => date('m.Y', $ts), 'count' => 0];
}
$rows = db()->query("SELECT strftime('%Y-%m', created_at) AS ym, COUNT(*) AS cnt FROM requests GROUP BY ym")->fetchAll();
foreach ($rows as $row) {
    if (isset($months[$row['ym']])) {
        $months[$row['ym']]['count'] = (int)$row['cnt'];
    }
}
$maxMonth = max(1, max(array_column($months, 'count')));

$statusLabels = ['new' => 'Новые', 'measurement' => 'Замер', 'in_progress' => 'В работе', 'completed' => 'Завершены', 'cancelled' => 'Отменены'];
$statusCounts = array_fill_keys(array_keys($statusLabels), 0);
foreach (db()->query('SELECT status, COUNT(*) cnt FROM requests GROUP BY status')->fetchAll() as $row) {
    $statusCounts[$row['status']] = (int)$row['cnt'];
}
$statusColors = ['new' => '#3f75a8', 'measurement' => '#e8a33d', 'in_progress' => '#7c63cf', 'completed' => '#3f8f7c', 'cancelled' => '#c8534a'];

$adminTitle = 'Обзор';
$adminBreadcrumb = 'Главная / Панель управления';
$activeAdmin = 'dashboard';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-stats">
    <div class="admin-stat">
        <div class="admin-stat__label">Всего заявок <svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5z"/></svg></div>
        <div class="admin-stat__num"><?= $totalRequests ?></div>
        <div class="admin-stat__trend"><b>+<?= $newRequests ?></b> новых за сегодня</div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat__label">В работе <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 5v5l4 2-.8 1.8L11 13V7h2z"/></svg></div>
        <div class="admin-stat__num"><?= $inProgress ?></div>
        <div class="admin-stat__trend">замеры и активные объекты</div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat__label">Завершено <svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg></div>
        <div class="admin-stat__num"><?= $completed ?></div>
        <div class="admin-stat__trend">выручка <b><?= format_price($completedBudget) ?></b></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat__label">Средний бюджет <svg viewBox="0 0 24 24"><path d="M3 5h18v14H3V5zm2 2v10h14V7H5zm2 2h4v2H7V9zm0 4h7v2H7v-2z"/></svg></div>
        <div class="admin-stat__num" style="font-size:24px"><?= format_price($avgBudget) ?></div>
        <div class="admin-stat__trend">по всем заявкам</div>
    </div>
</div>

<div class="admin-grid">
    <div class="panel">
        <div class="panel__head">
            <div><h2>Заявки по месяцам</h2><p>Количество обращений за последние 6 месяцев</p></div>
        </div>
        <div class="panel__body">
            <div class="chart-bars">
                <?php foreach ($months as $m): $height = (int)round($m['count'] / $maxMonth * 100); ?>
                    <div class="chart-bar" title="<?= e($m['label']) ?>: <?= $m['count'] ?>">
                        <div class="chart-bar__fill" style="height:<?= max(4, $height) ?>%"><span><?= $m['count'] ?></span></div>
                        <div class="chart-bar__label"><?= e($m['label']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><div><h2>Статусы заявок</h2><p>Распределение по этапам</p></div></div>
        <div class="panel__body">
            <div class="status-breakdown">
                <?php foreach ($statusLabels as $key => $label): $cnt = $statusCounts[$key]; $pct = $totalRequests ? (int)round($cnt / $totalRequests * 100) : 0; ?>
                    <div class="status-row">
                        <span class="status-row__label"><?= e($label) ?></span>
                        <span class="status-row__track"><span class="status-row__fill" style="width:<?= $pct ?>%;background:<?= $statusColors[$key] ?>"></span></span>
                        <span class="status-row__val"><?= $cnt ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="info-callout mt-4" style="font-size:12.5px">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                <div><?= $pendingReviews ?> <?= $pendingReviews === 1 ? 'отзыв ждёт' : 'отзывов ждут' ?> модерации. <a href="<?= url('admin/reviews.php') ?>" style="font-weight:700;text-decoration:underline">Перейти</a></div>
            </div>
        </div>
    </div>
</div>

<div class="admin-grid">
    <div class="panel">
        <div class="panel__head">
            <div><h2>Последние заявки</h2><p>Свежие обращения с сайта</p></div>
            <a class="btn btn--ghost btn--sm" href="<?= url('admin/requests.php') ?>">Все заявки</a>
        </div>
        <div class="panel__body panel__body--flush">
            <?php if (!$latestRequests): ?>
                <div class="empty-state"><div class="empty-state__icon">📋</div><h3>Заявок пока нет</h3></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Клиент</th><th>Тип</th><th>Бюджет</th><th>Статус</th></tr></thead>
                        <tbody>
                            <?php foreach ($latestRequests as $request): $badge = status_badge($request['status']); ?>
                                <tr onclick="window.location.href='<?= url('admin/requests.php?id=' . (int)$request['id']) ?>'" style="cursor:pointer">
                                    <td class="cell-main"><?= e($request['name']) ?><div class="cell-sub"><?= e($request['phone']) ?></div></td>
                                    <td><?= e($request['repair_type'] ?: '—') ?><div class="cell-sub"><?= e(format_date($request['created_at'])) ?></div></td>
                                    <td><?= $request['budget'] ? format_price((int)$request['budget']) : '—' ?></td>
                                    <td><span class="badge <?= e($badge['class']) ?>"><?= e($badge['label']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><div><h2>Отзывы на модерации</h2><p>Требуют проверки</p></div></div>
        <div class="panel__body" style="padding:8px 24px">
            <?php if (!$pendingList): ?>
                <div class="empty-state" style="padding:32px 0"><div class="empty-state__icon" style="font-size:30px">✅</div><p>Очередь модерации пуста</p></div>
            <?php else: ?>
                <?php foreach ($pendingList as $review): ?>
                    <div class="timeline-item" style="padding:16px 0">
                        <span class="timeline-item__dot" style="margin-top:8px"></span>
                        <div style="flex:1">
                            <strong><?= e($review['author_name']) ?> · <?= render_stars((int)$review['rating']) ?></strong>
                            <span><?= e(truncate_text($review['text'], 90)) ?></span>
                        </div>
                        <a class="btn btn--ghost btn--sm" href="<?= url('admin/reviews.php?id=' . (int)$review['id']) ?>">Проверить</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="admin-grid admin-grid--equal">
    <div class="panel">
        <div class="panel__head"><div><h2>Пользователи</h2><p>Недавно зарегистрированные</p></div></div>
        <div class="panel__body">
            <div class="list-timeline">
                <?php foreach ($recentUsers as $u): $rb = role_badge($u['role']); ?>
                    <div class="timeline-item">
                        <span class="avatar avatar--sm" style="background:<?= e($u['avatar_color']) ?>"><?= e(initials($u['name'])) ?></span>
                        <div style="flex:1"><strong><?= e($u['name']) ?></strong><span><?= e($u['email']) ?> · <?= e(format_date($u['created_at'])) ?></span></div>
                        <span class="badge <?= e($rb['class']) ?>"><?= e($rb['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($isAdmin): ?>
                <a class="btn btn--ghost btn--sm mt-2" href="<?= url('admin/users.php') ?>">Управление пользователями</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><div><h2>Быстрые действия</h2><p>Часто используемые разделы</p></div></div>
        <div class="panel__body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <a class="btn btn--ghost" href="<?= url('admin/requests.php?status=new') ?>">Новые заявки</a>
                <a class="btn btn--ghost" href="<?= url('admin/reviews.php?status=pending') ?>">Модерация</a>
                <?php if ($isAdmin): ?>
                    <a class="btn btn--ghost" href="<?= url('admin/services.php') ?>">Услуги</a>
                    <a class="btn btn--ghost" href="<?= url('admin/portfolio.php') ?>">Портфолио</a>
                    <a class="btn btn--ghost" href="<?= url('admin/promos.php') ?>">Акции</a>
                    <a class="btn btn--ghost" href="<?= url('admin/users.php') ?>">Пользователи</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
