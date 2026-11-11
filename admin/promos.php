<?php
require_once __DIR__ . '/../includes/config.php';

$user = current_user();
if (!$user) {
    redirect('login.php?redirect=admin/promos.php');
}
if ($user['role'] !== 'admin') {
    set_flash('error', 'Управление акциями доступно только администратору.');
    redirect('admin/index.php');
}

$errors = [];
$editId = (int)($_GET['edit'] ?? 0);
$isNew = isset($_GET['new']);
$item = ['id' => 0, 'title' => '', 'description' => '', 'discount' => '', 'badge' => 'Акция', 'valid_until' => date('Y-m-d', strtotime('+30 days')), 'is_active' => 1];

if ($editId) {
    $stmt = db()->prepare('SELECT * FROM promos WHERE id = ?');
    $stmt->execute([$editId]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('error', 'Акция не найдена.');
        redirect('admin/promos.php');
    }
    $item = $found;
}

if (is_post() && verify_csrf()) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete') {
        db()->prepare('DELETE FROM promos WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        cache_clear();
        set_flash('success', 'Акция удалена.');
        redirect('admin/promos.php');
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'discount' => trim((string)($_POST['discount'] ?? '')),
            'badge' => trim((string)($_POST['badge'] ?? 'Акция')) ?: 'Акция',
            'valid_until' => (string)($_POST['valid_until'] ?? ''),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if (mb_strlen($data['title'], 'UTF-8') < 5) {
            $errors['title'] = 'Название должно содержать минимум 5 символов.';
        }
        if ($data['description'] === '') {
            $errors['description'] = 'Добавьте описание акции.';
        }
        if ($data['discount'] === '') {
            $errors['discount'] = 'Укажите размер скидки или выгоды.';
        }
        if ($data['valid_until'] !== '' && !strtotime($data['valid_until'])) {
            $errors['valid_until'] = 'Укажите корректную дату.';
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE promos SET title=?, description=?, discount=?, badge=?, valid_until=?, is_active=? WHERE id=?');
                $stmt->execute([$data['title'], $data['description'], $data['discount'], $data['badge'], $data['valid_until'], $data['is_active'], $id]);
            } else {
                $stmt = db()->prepare('INSERT INTO promos (title, description, discount, badge, valid_until, is_active, created_at) VALUES (?,?,?,?,?,?,CURRENT_TIMESTAMP)');
                $stmt->execute([$data['title'], $data['description'], $data['discount'], $data['badge'], $data['valid_until'], $data['is_active']]);
            }
            cache_clear();
            set_flash('success', $id ? 'Акция обновлена.' : 'Акция добавлена.');
            redirect('admin/promos.php');
        }

        $item = array_merge($item, $data, ['id' => $id]);
        $editId = $id;
        $isNew = !$id;
    }
}

$promos = db()->query('SELECT * FROM promos ORDER BY is_active DESC, valid_until ASC')->fetchAll();

$adminTitle = ($editId || $isNew) ? ($editId ? 'Редактирование акции' : 'Новая акция') : 'Акции и скидки';
$adminBreadcrumb = 'Главная / Контент / Акции';
$activeAdmin = 'promos';
require __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($editId || $isNew): ?>
    <div class="flex-between mb-3 flex-wrap">
        <a class="link-arrow" href="<?= url('admin/promos.php') ?>">← Назад к акциям</a>
        <a class="btn btn--ghost btn--sm" href="<?= url('promos.php') ?>" target="_blank">Посмотреть на сайте</a>
    </div>
    <?php if ($errors): ?><div class="flash flash--error mb-3">Исправьте ошибки в форме.</div><?php endif; ?>

    <div class="panel">
        <div class="panel__head"><div><h2><?= $editId ? 'Редактирование акции' : 'Добавление акции' ?></h2><p>Акция появится на главной и на странице «Акции»</p></div></div>
        <div class="panel__body">
            <form method="post" action="<?= url('admin/promos.php' . ($editId ? '?edit=' . $editId : '?new=1')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
                <div class="form-grid">
                    <div class="field form-row--full">
                        <label for="pr-title">Название акции <span class="req">*</span></label>
                        <input type="text" id="pr-title" name="title" value="<?= e($item['title']) ?>" required>
                        <?php if (isset($errors['title'])): ?><span class="field__error"><?= e($errors['title']) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="pr-discount">Скидка / выгода <span class="req">*</span></label>
                        <input type="text" id="pr-discount" name="discount" value="<?= e($item['discount']) ?>" placeholder="−15% или 0 ₽">
                        <?php if (isset($errors['discount'])): ?><span class="field__error"><?= e($errors['discount']) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="pr-badge">Метка</label>
                        <input type="text" id="pr-badge" name="badge" value="<?= e($item['badge']) ?>" placeholder="Хит сезона">
                    </div>
                    <div class="field">
                        <label for="pr-until">Действует до</label>
                        <input type="date" id="pr-until" name="valid_until" value="<?= e($item['valid_until']) ?>">
                        <?php if (isset($errors['valid_until'])): ?><span class="field__error"><?= e($errors['valid_until']) ?></span><?php endif; ?>
                    </div>
                    <div class="field field--check" style="flex-direction:row;align-items:center;padding-top:22px">
                        <input type="checkbox" id="pr-active" name="is_active" value="1" <?= (int)$item['is_active'] === 1 ? 'checked' : '' ?>>
                        <label for="pr-active">Акция активна</label>
                    </div>
                    <div class="field form-row--full">
                        <label for="pr-desc">Описание акции <span class="req">*</span></label>
                        <textarea id="pr-desc" name="description" rows="6" required><?= e($item['description']) ?></textarea>
                        <?php if (isset($errors['description'])): ?><span class="field__error"><?= e($errors['description']) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn--primary" type="submit"><?= $editId ? 'Сохранить изменения' : 'Добавить акцию' ?></button>
                    <a class="btn btn--ghost" href="<?= url('admin/promos.php') ?>">Отмена</a>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <div class="flex-between mb-3 flex-wrap">
        <p class="text-muted" style="margin:0;font-size:13.5px">Всего акций: <?= count($promos) ?>. Просроченные автоматически переносятся в архив на сайте.</p>
        <a class="btn btn--primary" href="<?= url('admin/promos.php?new=1') ?>">+ Добавить акцию</a>
    </div>
    <div class="panel">
        <div class="panel__body panel__body--flush">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Акция</th><th>Скидка</th><th>Метка</th><th>Действует до</th><th>Статус</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($promos as $p): ?>
                            <tr>
                                <td class="cell-main"><?= e($p['title']) ?><div class="cell-sub"><?= e(truncate_text($p['description'], 70)) ?></div></td>
                                <td><strong><?= e($p['discount']) ?></strong></td>
                                <td><span class="badge badge--plain" style="background:var(--bg)"><?= e($p['badge']) ?></span></td>
                                <td><?= e(format_date($p['valid_until'])) ?></td>
                                <td>
                                    <?php if (!(int)$p['is_active']): ?>
                                        <span class="badge status-cancelled">Выключена</span>
                                    <?php elseif ($p['valid_until'] && strtotime($p['valid_until']) < strtotime('today')): ?>
                                        <span class="badge status-measurement">Истекла</span>
                                    <?php else: ?>
                                        <span class="badge status-completed">Активна</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="cell-actions">
                                        <a class="icon-btn" href="<?= url('admin/promos.php?edit=' . (int)$p['id']) ?>" title="Редактировать"><svg viewBox="0 0 24 24"><path d="M3 17.2V21h3.8L18 9.8 14.2 6 3 17.2zM20.7 7.1c.4-.4.4-1 0-1.4l-2.4-2.4c-.4-.4-1-.4-1.4 0l-1.9 1.9L18.8 9l1.9-1.9z"/></svg></a>
                                        <form method="post" action="<?= url('admin/promos.php') ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button class="icon-btn icon-btn--danger" type="submit" title="Удалить" data-confirm="Удалить акцию «<?= e($p['title']) ?>»?"><svg viewBox="0 0 24 24"><path d="M6 7h12v14H6V7zm3-4h6l1 2h4v2H4V5h4l1-2zm1 7v8h2V10h-2zm4 0v8h2V10h-2z"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
