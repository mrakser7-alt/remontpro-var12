<?php
require_once __DIR__ . '/../includes/config.php';

$user = current_user();
if (!$user) {
    redirect('login.php?redirect=admin/messages.php');
}
if ($user['role'] !== 'admin') {
    set_flash('error', 'Сообщения обратной связи доступны только администратору.');
    redirect('admin/index.php');
}

if (is_post() && verify_csrf()) {
    $action = (string)($_POST['action'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'mark_read') {
        db()->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
        set_flash('success', 'Сообщение отмечено как прочитанное.');
    } elseif ($action === 'mark_unread') {
        db()->prepare('UPDATE contact_messages SET is_read = 0 WHERE id = ?')->execute([$id]);
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
        set_flash('success', 'Сообщение удалено.');
    }
    redirect('admin/messages.php' . (!empty($_POST['return_filter']) ? '?filter=' . urlencode((string)$_POST['return_filter']) : ''));
}

$filter = (string)($_GET['filter'] ?? '');
if (!in_array($filter, ['unread', 'read'], true)) {
    $filter = '';
}
$where = $filter === 'unread' ? 'WHERE is_read = 0' : ($filter === 'read' ? 'WHERE is_read = 1' : '');
$messages = db()->query("SELECT * FROM contact_messages $where ORDER BY is_read ASC, created_at DESC")->fetchAll();
$unread = (int)db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
$read = (int)db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 1')->fetchColumn();

$adminTitle = 'Сообщения с сайта';
$adminBreadcrumb = 'Главная / Сообщения';
$activeAdmin = 'messages';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="filter-bar">
    <a class="filter-chip<?= $filter === '' ? ' is-active' : '' ?>" href="<?= url('admin/messages.php') ?>">Все <span><?= $unread + $read ?></span></a>
    <a class="filter-chip<?= $filter === 'unread' ? ' is-active' : '' ?>" href="<?= url('admin/messages.php?filter=unread') ?>">Непрочитанные <span><?= $unread ?></span></a>
    <a class="filter-chip<?= $filter === 'read' ? ' is-active' : '' ?>" href="<?= url('admin/messages.php?filter=read') ?>">Прочитанные <span><?= $read ?></span></a>
</div>

<?php if (!$messages): ?>
    <div class="panel"><div class="empty-state">
        <div class="empty-state__icon">📭</div>
        <h3>Сообщений нет</h3>
        <p>Обращения через форму на странице контактов будут появляться здесь.</p>
    </div></div>
<?php else: ?>
    <div style="display:grid;gap:14px">
        <?php foreach ($messages as $message): ?>
            <div class="panel" style="<?= !(int)$message['is_read'] ? 'border-color:#e8d5b0;box-shadow:0 2px 12px rgba(232,163,61,.08)' : '' ?>">
                <div class="panel__head">
                    <div style="display:flex;align-items:center;gap:12px">
                        <span class="avatar" style="background:<?= !(int)$message['is_read'] ? '#e8a33d' : '#9aa0ab' ?>"><?= e(initials($message['name'])) ?></span>
                        <div>
                            <h3 style="font-size:15px"><?= e($message['name']) ?> <span class="text-muted" style="font-weight:500">· <?= e($message['subject']) ?></span></h3>
                            <div class="cell-sub"><?= e(format_date($message['created_at'])) ?> · <?= e($message['email'] ?: 'без e-mail') ?><?= $message['phone'] ? ' · ' . e($message['phone']) : '' ?></div>
                        </div>
                    </div>
                    <?php if (!(int)$message['is_read']): ?><span class="badge status-new">Новое</span><?php endif; ?>
                </div>
                <div class="panel__body">
                    <p style="font-size:14px;color:var(--ink-3)"><?= nl2br(e($message['message'])) ?></p>
                    <div class="form-actions" style="margin-top:16px">
                        <?php if ($message['email']): ?><a class="btn btn--ghost btn--sm" href="mailto:<?= e($message['email']) ?>?subject=Re: <?= e($message['subject']) ?>">Ответить на e-mail</a><?php endif; ?>
                        <form method="post" action="<?= url('admin/messages.php') ?>" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="<?= (int)$message['is_read'] ? 'mark_unread' : 'mark_read' ?>">
                            <input type="hidden" name="id" value="<?= (int)$message['id'] ?>">
                            <input type="hidden" name="return_filter" value="<?= e($filter) ?>">
                            <button class="btn btn--ghost btn--sm" type="submit"><?= (int)$message['is_read'] ? 'Вернуть в непрочитанные' : 'Отметить прочитанным' ?></button>
                        </form>
                        <form method="post" action="<?= url('admin/messages.php') ?>" style="display:inline;margin-left:auto">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$message['id'] ?>">
                            <input type="hidden" name="return_filter" value="<?= e($filter) ?>">
                            <button class="btn btn--danger btn--sm" type="submit" data-confirm="Удалить сообщение?">Удалить</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
