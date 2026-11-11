<?php
require_once __DIR__ . '/config.php';

$user = current_user();
if (!$user) {
    set_flash('warning', 'Войдите как сотрудник, чтобы открыть админ-панель.');
    redirect('login.php?redirect=admin/index.php');
}
if (!in_array($user['role'], ['admin', 'manager'], true)) {
    set_flash('error', 'Доступ к админ-панели есть только у администраторов и менеджеров.');
    redirect('cabinet.php');
}

$adminTitle = $adminTitle ?? 'Панель управления';
$adminBreadcrumb = $adminBreadcrumb ?? 'Админ-панель';
$activeAdmin = $activeAdmin ?? 'dashboard';
$isAdmin = $user['role'] === 'admin';

$navNewRequests = (int)db()->query("SELECT COUNT(*) FROM requests WHERE status = 'new'")->fetchColumn();
$navPendingReviews = (int)db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
$navUnreadMessages = (int)db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
$role = role_badge($user['role']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($adminTitle) ?> | Админ-панель РемонтПро</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="<?= url('assets/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=13">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-side" id="adminSide">
        <a class="logo logo--light" href="<?= url('admin/index.php') ?>">
            <span class="logo__mark" aria-hidden="true">
                <svg viewBox="0 0 48 48"><rect width="48" height="48" rx="14" fill="#e8a33d"/><path d="M14 31.5V22l10-6.5 10 6.5v9.5" fill="none" stroke="#14181f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 33v-7h8v7" fill="none" stroke="#14181f" stroke-width="3" stroke-linejoin="round"/></svg>
            </span>
            <span class="logo__text"><strong>Ремонт<span>Про</span></strong><small>панель управления</small></span>
        </a>

        <div class="admin-side__label">Работа</div>
        <nav class="admin-nav">
            <a class="<?= $activeAdmin === 'dashboard' ? 'is-active' : '' ?>" href="<?= url('admin/index.php') ?>">
                <svg viewBox="0 0 24 24"><path d="M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z"/></svg> Обзор
            </a>
            <a class="<?= $activeAdmin === 'requests' ? 'is-active' : '' ?>" href="<?= url('admin/requests.php') ?>">
                <svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg> Заявки
                <?php if ($navNewRequests): ?><span class="count"><?= $navNewRequests ?></span><?php endif; ?>
            </a>
            <a class="<?= $activeAdmin === 'reviews' ? 'is-active' : '' ?>" href="<?= url('admin/reviews.php') ?>">
                <svg viewBox="0 0 24 24"><path d="M4 4h16v12H8l-4 4V4z"/></svg> Отзывы
                <?php if ($navPendingReviews): ?><span class="count"><?= $navPendingReviews ?></span><?php endif; ?>
            </a>
            <?php if ($isAdmin): ?>
            <a class="<?= $activeAdmin === 'messages' ? 'is-active' : '' ?>" href="<?= url('admin/messages.php') ?>">
                <svg viewBox="0 0 24 24"><path d="M3 6.5A2.5 2.5 0 015.5 4h13A2.5 2.5 0 0121 6.5v11a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 17.5v-11zm2.2-.5L12 11l6.8-5H5.2z"/></svg> Сообщения
                <?php if ($navUnreadMessages): ?><span class="count"><?= $navUnreadMessages ?></span><?php endif; ?>
            </a>
            <?php endif; ?>
        </nav>

        <?php if ($isAdmin): ?>
            <div class="admin-side__label">Контент сайта</div>
            <nav class="admin-nav">
                <a class="<?= $activeAdmin === 'services' ? 'is-active' : '' ?>" href="<?= url('admin/services.php') ?>">
                    <svg viewBox="0 0 24 24"><path d="M14.7 2l7.3 7.3-2.8 2.8-2.1-2.1-8.9 8.9a3 3 0 11-4.2-4.2l8.9-8.9-2.1-2.1L14.7 2z"/></svg> Услуги
                </a>
                <a class="<?= $activeAdmin === 'portfolio' ? 'is-active' : '' ?>" href="<?= url('admin/portfolio.php') ?>">
                    <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4V4zm2 2v12h12V6H6zm2 2h8v2H8V8zm0 4h8v2H8v-2zm0 4h5v2H8v-2z"/></svg> Портфолио
                </a>
                <a class="<?= $activeAdmin === 'promos' ? 'is-active' : '' ?>" href="<?= url('admin/promos.php') ?>">
                    <svg viewBox="0 0 24 24"><path d="M12 2l2.4 6.9H22l-6.1 4.4 2.3 7.2-6.2-4.4-6.2 4.4 2.3-7.2L2 8.9h7.6L12 2z"/></svg> Акции
                </a>
            </nav>

            <div class="admin-side__label">Администрирование</div>
            <nav class="admin-nav">
                <a class="<?= $activeAdmin === 'users' ? 'is-active' : '' ?>" href="<?= url('admin/users.php') ?>">
                    <svg viewBox="0 0 24 24"><path d="M9 11a4 4 0 100-8 4 4 0 000 8zm0 2c-3.3 0-6 1.8-6 4v3h12v-3c0-2.2-2.7-4-6-4zm8-2a3 3 0 100-6 3 3 0 000 6zm0 2c-.8 0-1.6.1-2.2.4 1.4 1 2.2 2.3 2.2 3.6v3h5v-2.5c0-2.2-2.2-4.5-5-4.5z"/></svg> Пользователи
                </a>
            </nav>
        <?php endif; ?>

        <div class="admin-side__foot">
            <div class="admin-side__user">
                <span class="avatar avatar--sm" style="background:<?= e($user['avatar_color']) ?>"><?= e(initials($user['name'])) ?></span>
                <div>
                    <strong><?= e($user['name']) ?></strong>
                    <span><?= e($role['label']) ?></span>
                </div>
            </div>
            <a class="back-site" href="<?= url('index.php') ?>">
                <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg> На сайт
            </a>
            <a class="back-site" href="<?= url('logout.php?token=' . urlencode(csrf_token())) ?>">
                <svg viewBox="0 0 24 24"><path d="M10 3H4v18h6v-2H6V5h4V3zm5.6 4.6L14.2 9l2 2H9v2h7.2l-2 2 1.4 1.4L20 12l-4.4-4.4z"/></svg> Выйти
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:14px">
                <button class="burger admin-mobile-toggle" id="adminToggle" type="button" aria-label="Меню админки">
                    <span></span><span></span><span></span>
                </button>
                <div>
                    <div class="admin-topbar__crumbs"><?= e($adminBreadcrumb) ?></div>
                    <h1><?= e($adminTitle) ?></h1>
                </div>
            </div>
            <div class="admin-topbar__right">
                <a class="btn btn--primary btn--sm" href="<?= url('admin/requests.php?status=new') ?>">Новые заявки <?= $navNewRequests ? '(' . $navNewRequests . ')' : '' ?></a>
                <a class="account-chip" href="<?= url('cabinet.php') ?>">
                    <span class="avatar avatar--sm" style="background:<?= e($user['avatar_color']) ?>"><?= e(initials($user['name'])) ?></span>
                    <span class="account-chip__name"><?= e(mb_substr($user['name'], 0, 14, 'UTF-8')) ?></span>
                </a>
            </div>
        </header>
        <div class="admin-content">
            <?php foreach (get_flashes() as $flash): ?>
                <div class="flash flash--<?= e($flash['type']) ?> mb-3" role="status">
                    <span><?= e($flash['message']) ?></span>
                    <button class="flash__close" type="button" aria-label="Закрыть">&times;</button>
                </div>
            <?php endforeach; ?>
