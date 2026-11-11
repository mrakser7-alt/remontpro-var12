<?php
require_once __DIR__ . '/../includes/config.php';

$user = current_user();
if (!$user) {
    redirect('login.php?redirect=admin/reviews.php');
}
if (!in_array($user['role'], ['admin', 'manager'], true)) {
    set_flash('error', 'Недостаточно прав для доступа к отзывам.');
    redirect('cabinet.php');
}
$isAdmin = $user['role'] === 'admin';

if (is_post() && verify_csrf()) {
    $action = (string)($_POST['action'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete_review') {
        if (!$isAdmin) {
            set_flash('error', 'Удалять отзывы может только администратор.');
        } else {
            db()->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
            cache_clear();
            set_flash('success', 'Отзыв удалён.');
        }
        redirect('admin/reviews.php');
    }

    if (in_array($action, ['approve', 'reject'], true)) {
        if (!$isAdmin) {
            set_flash('error', 'Модерировать отзывы может только администратор.');
        } else {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            db()->prepare('UPDATE reviews SET status = ? WHERE id = ?')->execute([$status, $id]);
            cache_clear();
            set_flash('success', $action === 'approve' ? 'Отзыв опубликован на сайте.' : 'Отзыв отклонён.');
        }
        redirect('admin/reviews.php' . (!empty($_POST['return_status']) ? '?status=' . urlencode((string)$_POST['return_status']) : ''));
    }
}

$filterStatus = (string)($_GET['status'] ?? '');
if (!in_array($filterStatus, ['pending', 'approved', 'rejected'], true)) {
    $filterStatus = '';
}

$where = '1=1';
$params = [];
if ($filterStatus !== '') {
    $where .= ' AND status = ?';
    $params[] = $filterStatus;
}
$stmt = db()->prepare("SELECT * FROM reviews WHERE $where ORDER BY CASE status WHEN 'pending' THEN 0 ELSE 1 END, created_at DESC");
$stmt->execute($params);
$reviews = $stmt->fetchAll();

$counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
foreach (db()->query('SELECT status, COUNT(*) cnt FROM reviews GROUP BY status')->fetchAll() as $row) {
    if (isset($counts[$row['status']])) {
        $counts[$row['status']] = (int)$row['cnt'];
    }
    $counts['all'] += (int)$row['cnt'];
}

$adminTitle = 'Модерация отзывов';
$adminBreadcrumb = 'Главная / Отзывы';
$activeAdmin = 'reviews';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="info-callout mb-3">
    <svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3z"/></svg>
    <div>
        <?php if ($isAdmin): ?>
            Проверяйте отзывы перед публикацией. После одобрения отзыв появляется на странице «Отзывы» и на главной.
        <?php else: ?>
            Вы вошли как менеджер: отзывы доступны для просмотра, но модерация и удаление — только для администратора.
        <?php endif; ?>
    </div>
</div>

<div class="filter-bar">
    <a class="filter-chip<?= $filterStatus === '' ? ' is-active' : '' ?>" href="<?= url('admin/reviews.php') ?>">Все <span><?= $counts['all'] ?></span></a>
    <a class="filter-chip<?= $filterStatus === 'pending' ? ' is-active' : '' ?>" href="<?= url('admin/reviews.php?status=pending') ?>">На модерации <span><?= $counts['pending'] ?></span></a>
    <a class="filter-chip<?= $filterStatus === 'approved' ? ' is-active' : '' ?>" href="<?= url('admin/reviews.php?status=approved') ?>">Опубликованные <span><?= $counts['approved'] ?></span></a>
    <a class="filter-chip<?= $filterStatus === 'rejected' ? ' is-active' : '' ?>" href="<?= url('admin/reviews.php?status=rejected') ?>">Отклонённые <span><?= $counts['rejected'] ?></span></a>
</div>

<?php if (!$reviews): ?>
    <div class="panel"><div class="empty-state">
        <div class="empty-state__icon">✅</div>
        <h3>Отзывов в этом разделе нет</h3>
        <p>Все отзывы обработаны.</p>
    </div></div>
<?php else: ?>
    <div style="display:grid;gap:16px">
        <?php foreach ($reviews as $review): $rb = review_status_badge($review['status']); ?>
            <div class="panel">
                <div class="panel__head">
                    <div style="display:flex;align-items:center;gap:12px">
                        <span class="avatar" style="background:<?= e(['#8a9a5b', '#c76b8e', '#6f7fd6', '#4d9d8a', '#b8823d'][$review['id'] % 5]) ?>"><?= e(initials($review['author_name'])) ?></span>
                        <div>
                            <h3 style="font-size:15px"><?= e($review['author_name']) ?> <span class="text-muted" style="font-weight:500">· <?= e($review['city']) ?></span></h3>
                            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                                <?= render_stars((int)$review['rating']) ?>
                                <span class="cell-sub"><?= e(format_date($review['created_at'])) ?></span>
                                <?php if ($review['service']): ?><span class="cell-sub">· <?= e($review['service']) ?></span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <span class="badge <?= e($rb['class']) ?>"><?= e($rb['label']) ?></span>
                </div>
                <div class="panel__body">
                    <p style="font-size:14px;color:var(--ink-3)"><?= nl2br(e($review['text'])) ?></p>
                    <?php if ($isAdmin): ?>
                        <div class="form-actions" style="margin-top:18px">
                            <?php if ($review['status'] !== 'approved'): ?>
                                <form method="post" action="<?= url('admin/reviews.php') ?>" style="display:inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
                                    <input type="hidden" name="return_status" value="<?= e($filterStatus) ?>">
                                    <button class="btn btn--success btn--sm" type="submit">Опубликовать</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($review['status'] !== 'rejected'): ?>
                                <form method="post" action="<?= url('admin/reviews.php') ?>" style="display:inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
                                    <input type="hidden" name="return_status" value="<?= e($filterStatus) ?>">
                                    <button class="btn btn--ghost btn--sm" type="submit">Отклонить</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="<?= url('admin/reviews.php') ?>" style="display:inline;margin-left:auto">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_review">
                                <input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
                                <button class="btn btn--danger btn--sm" type="submit" data-confirm="Удалить отзыв без возможности восстановления?">Удалить</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
