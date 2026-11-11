<?php
require_once __DIR__ . '/config.php';

$user = current_user();
$pageTitle = $pageTitle ?? 'Профессиональный ремонт квартир и коттеджей';
$pageDescription = $pageDescription ?? 'РемонтПро — профессиональный ремонт квартир и коттеджей под ключ в Москве и области. Фиксированная смета, гарантия 3 года, собственные бригады.';
$activeMenu = $activeMenu ?? '';
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | РемонтПро</title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#14181f">
    <link rel="icon" href="<?= url('assets/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=13">
</head>
<body class="<?= e($bodyClass) ?>">

<div class="topbar">
    <div class="container topbar__inner">
        <div class="topbar__left">
            <a class="topbar__item" href="tel:+74951204500">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.2 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.2 1.1l-2.3 1.9z"/></svg>
                +7 (495) 120-45-00
            </a>
            <a class="topbar__item topbar__item--muted" href="mailto:info@remontpro.ru">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6.5A2.5 2.5 0 015.5 4h13A2.5 2.5 0 0121 6.5v11a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 17.5v-11zm2.2-.5L12 11l6.8-5H5.2zM19 7.9l-6.4 4.7a1 1 0 01-1.2 0L5 7.9V17.5c0 .3.2.5.5.5h13c.3 0 .5-.2.5-.5V7.9z"/></svg>
                info@remontpro.ru
            </a>
        </div>
        <div class="topbar__right">
            <span class="topbar__item topbar__item--muted topbar__hours">Пн–Вс: 9:00–21:00</span>
            <a class="topbar__item topbar__admin" href="<?= url('team.php') ?>">Команда проекта</a>
        </div>
    </div>
</div>

<header class="site-header" id="siteHeader">
    <div class="container header__inner">
        <a class="logo" href="<?= url('index.php') ?>" aria-label="РемонтПро — на главную">
            <span class="logo__mark" aria-hidden="true">
                <svg viewBox="0 0 48 48"><rect width="48" height="48" rx="14" fill="#e8a33d"/><path d="M14 31.5V22l10-6.5 10 6.5v9.5" fill="none" stroke="#14181f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 33v-7h8v7" fill="none" stroke="#14181f" stroke-width="3" stroke-linejoin="round"/></svg>
            </span>
            <span class="logo__text">
                <strong>Ремонт<span>Про</span></strong>
                <small>квартиры и коттеджи</small>
            </span>
        </a>

        <nav class="nav" id="mainNav" aria-label="Основная навигация">
            <a class="nav__link<?= $activeMenu === 'about' ? ' is-active' : '' ?>" href="<?= url('about.php') ?>">О компании</a>
            <a class="nav__link<?= $activeMenu === 'services' ? ' is-active' : '' ?>" href="<?= url('services.php') ?>">Услуги</a>
            <a class="nav__link<?= $activeMenu === 'portfolio' ? ' is-active' : '' ?>" href="<?= url('portfolio.php') ?>">Портфолио</a>
            <a class="nav__link<?= $activeMenu === 'reviews' ? ' is-active' : '' ?>" href="<?= url('reviews.php') ?>">Отзывы</a>
            <a class="nav__link<?= $activeMenu === 'promos' ? ' is-active' : '' ?>" href="<?= url('promos.php') ?>">Акции</a>
            <a class="nav__link<?= $activeMenu === 'contacts' ? ' is-active' : '' ?>" href="<?= url('contacts.php') ?>">Контакты</a>
            <a class="nav__link nav__link--accent<?= $activeMenu === 'request' ? ' is-active' : '' ?>" href="<?= url('request.php') ?>">Заявка на ремонт</a>
            <a class="nav__link<?= $activeMenu === 'cabinet' ? ' is-active' : '' ?>" href="<?= url($user ? 'cabinet.php' : 'login.php') ?>">
                Личный кабинет
            </a>
        </nav>

        <div class="header__actions">
            <?php if ($user): ?>
                <a class="account-chip" href="<?= url('cabinet.php') ?>" title="Личный кабинет">
                    <span class="avatar avatar--sm" style="background:<?= e($user['avatar_color']) ?>"><?= e(initials($user['name'])) ?></span>
                    <span class="account-chip__name"><?= e(mb_substr($user['name'], 0, 14, 'UTF-8')) ?></span>
                </a>
            <?php else: ?>
                <a class="btn btn--ghost btn--sm" href="<?= url('register.php') ?>">Регистрация</a>
            <?php endif; ?>
            <?php if ($user && in_array($user['role'], ['admin', 'manager'], true)): ?>
                <a class="btn btn--dark btn--sm" href="<?= url('admin/index.php') ?>">Админ-панель</a>
            <?php endif; ?>
            <button class="burger" id="burger" aria-label="Открыть меню" aria-expanded="false" aria-controls="mainNav">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>
<div class="nav-overlay" id="navOverlay" hidden></div>

<main id="main">
    <?php $flashes = get_flashes(); ?>
    <?php if ($flashes): ?>
        <div class="container flash-stack">
            <?php foreach ($flashes as $flash): ?>
                <div class="flash flash--<?= e($flash['type']) ?>" role="status">
                    <span><?= e($flash['message']) ?></span>
                    <button class="flash__close" type="button" aria-label="Закрыть">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
