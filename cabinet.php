<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/forms.php';

require_login();

$user = current_user();
$tab = (string)($_GET['tab'] ?? 'overview');
if (!in_array($tab, ['overview', 'requests', 'profile', 'security'], true)) {
    $tab = 'overview';
}

$profileErrors = [];
$passwordErrors = [];

// ---------- Обновление профиля ----------
if (is_post() && ($_POST['action'] ?? '') === 'update_profile') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));

    if (!verify_csrf()) {
        $profileErrors['csrf'] = 'Срок действия формы истёк.';
    }
    if (mb_strlen($name, 'UTF-8') < 2) {
        $profileErrors['name'] = 'Укажите имя (минимум 2 символа).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profileErrors['email'] = 'Укажите корректный e-mail.';
    }
    if (strlen(preg_replace('/\D/', '', $phone) ?? '') < 10) {
        $profileErrors['phone'] = 'Укажите корректный телефон.';
    }
    if (!$profileErrors) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user['id']]);
        if ((int)$stmt->fetchColumn() > 0) {
            $profileErrors['email'] = 'Этот e-mail уже занят другим пользователем.';
        }
    }
    if (!$profileErrors) {
        $stmt = db()->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
        $stmt->execute([$name, $email, $phone, $user['id']]);
        set_flash('success', 'Профиль обновлён.');
        redirect('cabinet.php?tab=profile');
    }
    $tab = 'profile';
}

// ---------- Смена пароля ----------
if (is_post() && ($_POST['action'] ?? '') === 'change_password') {
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['new_password_confirm'] ?? '');

    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $hash = (string)$stmt->fetchColumn();

    if (!verify_csrf()) {
        $passwordErrors['csrf'] = 'Срок действия формы истёк.';
    }
    if (!password_verify($current, $hash)) {
        $passwordErrors['current_password'] = 'Текущий пароль указан неверно.';
    }
    if (strlen($new) < 8 || !preg_match('/[A-Za-zА-Яа-я]/u', $new) || !preg_match('/\d/', $new)) {
        $passwordErrors['new_password'] = 'Новый пароль: минимум 8 символов, буквы и цифры.';
    }
    if ($new !== $confirm) {
        $passwordErrors['new_password_confirm'] = 'Пароли не совпадают.';
    }
    if (!$passwordErrors) {
        $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        set_flash('success', 'Пароль изменён. Используйте новый пароль при следующем входе.');
        redirect('cabinet.php?tab=security');
    }
    $tab = 'security';
}

// ---------- Данные кабинета ----------
$stmt = db()->prepare('SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();

$totalRequests = count($requests);
$activeRequests = count(array_filter($requests, fn($r) => in_array($r['status'], ['new', 'measurement', 'in_progress'], true)));
$completedRequests = count(array_filter($requests, fn($r) => $r['status'] === 'completed'));
$totalBudget = array_sum(array_map(fn($r) => (int)$r['budget'], $requests));

$stmt = db()->prepare('SELECT * FROM reviews WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$myReviews = $stmt->fetchAll();

$role = role_badge($user['role']);

$pageTitle = 'Личный кабинет';
$pageDescription = 'Личный кабинет РемонтПро: история заявок, статусы ремонта и управление профилем.';
$activeMenu = 'cabinet';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="padding:36px 0 44px">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>Личный кабинет</span>
        </nav>
        <span class="eyebrow">Личный кабинет</span>
        <h1 style="font-size:clamp(1.8rem, 3.2vw, 2.6rem)">Здравствуйте, <?= e(explode(' ', $user['name'])[0]) ?>!</h1>
        <p>Здесь собрана вся информация о ваших заявках и ремонте.</p>
    </div>
</section>

<div class="container cabinet-layout">
    <aside class="cabinet-side">
        <div class="cabinet-user">
            <span class="avatar avatar--lg" style="background:<?= e($user['avatar_color']) ?>;margin:0 auto"><?= e(initials($user['name'])) ?></span>
            <h2><?= e($user['name']) ?></h2>
            <p><?= e($user['email']) ?></p>
            <span class="badge <?= e($role['class']) ?> mt-1"><?= e($role['label']) ?></span>
        </div>
        <nav class="cabinet-nav" aria-label="Разделы кабинета">
            <a class="<?= $tab === 'overview' ? 'is-active' : '' ?>" href="<?= url('cabinet.php?tab=overview') ?>">
                <svg viewBox="0 0 24 24"><path d="M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z"/></svg> Обзор
            </a>
            <a class="<?= $tab === 'requests' ? 'is-active' : '' ?>" href="<?= url('cabinet.php?tab=requests') ?>">
                <svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg> Мои заявки
                <?php if ($totalRequests): ?><span style="margin-left:auto;font-size:11px;color:var(--muted)"><?= $totalRequests ?></span><?php endif; ?>
            </a>
            <a class="<?= $tab === 'profile' ? 'is-active' : '' ?>" href="<?= url('cabinet.php?tab=profile') ?>">
                <svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.4 0-8 2.2-8 5v3h16v-3c0-2.8-3.6-5-8-5z"/></svg> Профиль
            </a>
            <a class="<?= $tab === 'security' ? 'is-active' : '' ?>" href="<?= url('cabinet.php?tab=security') ?>">
                <svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3zm-1.2 14.4l5.7-5.7-1.4-1.4-4.3 4.3-2-2-1.4 1.4 3.4 3.4z"/></svg> Безопасность
            </a>
            <div class="nav-divider"></div>
            <a href="<?= url('reviews.php#leave-review') ?>">
                <svg viewBox="0 0 24 24"><path d="M4 4h16v12H8l-4 4V4z"/></svg> Оставить отзыв
            </a>
            <a href="<?= url('request.php') ?>">
                <svg viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5z"/></svg> Новая заявка
            </a>
            <?php if (in_array($user['role'], ['admin', 'manager'], true)): ?>
                <a href="<?= url('admin/index.php') ?>">
                    <svg viewBox="0 0 24 24"><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm4 3v2h8v-2H8z"/></svg> Админ-панель
                </a>
            <?php endif; ?>
            <div class="nav-divider"></div>
            <a href="<?= url('logout.php?token=' . urlencode(csrf_token())) ?>">
                <svg viewBox="0 0 24 24"><path d="M10 3H4v18h6v-2H6V5h4V3zm5.6 4.6L14.2 9l2 2H9v2h7.2l-2 2 1.4 1.4L20 12l-4.4-4.4z"/></svg> Выйти
            </a>
        </nav>
    </aside>

    <div class="cabinet-main">
        <?php if ($tab === 'overview'): ?>
            <div class="dash-cards">
                <div class="dash-card">
                    <div class="dash-card__top">
                        <span class="dash-card__icon"><svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5z"/></svg></span>
                    </div>
                    <div class="dash-card__num"><?= $totalRequests ?></div>
                    <div class="dash-card__label">всего заявок</div>
                </div>
                <div class="dash-card">
                    <div class="dash-card__top">
                        <span class="dash-card__icon" style="background:#ede9fb"><svg viewBox="0 0 24 24" style="fill:#6146b8"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 5v5l4 2-.8 1.8L11 13V7h2z"/></svg></span>
                    </div>
                    <div class="dash-card__num"><?= $activeRequests ?></div>
                    <div class="dash-card__label">в работе сейчас</div>
                </div>
                <div class="dash-card">
                    <div class="dash-card__top">
                        <span class="dash-card__icon" style="background:var(--teal-soft)"><svg viewBox="0 0 24 24" style="fill:var(--teal)"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg></span>
                    </div>
                    <div class="dash-card__num"><?= $completedRequests ?></div>
                    <div class="dash-card__label">завершено проектов</div>
                </div>
            </div>

            <?php if ($completedRequests > 0 && !$myReviews): ?>
                <div class="info-callout">
                    <svg viewBox="0 0 24 24"><path d="M12 2l2.4 6.9H22l-6.1 4.4 2.3 7.2-6.2-4.4-6.2 4.4 2.3-7.2L2 8.9h7.6L12 2z"/></svg>
                    <div>Ваш ремонт завершён! Будем благодарны за отзыв — это помогает нам становиться лучше. <a href="<?= url('reviews.php#leave-review') ?>" style="font-weight:700;text-decoration:underline">Оставить отзыв</a></div>
                </div>
            <?php endif; ?>

            <div class="panel">
                <div class="panel__head">
                    <div>
                        <h2>Последние заявки</h2>
                        <p>Статусы обновляются менеджером после каждого этапа</p>
                    </div>
                    <a class="btn btn--ghost btn--sm" href="<?= url('cabinet.php?tab=requests') ?>">Все заявки</a>
                </div>
                <div class="panel__body panel__body--flush">
                    <?php if (!$requests): ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">📋</div>
                            <h3>Заявок пока нет</h3>
                            <p>Оставьте первую заявку — рассчитаем стоимость ремонта бесплатно.</p>
                            <p class="mt-3"><a class="btn btn--primary" href="<?= url('request.php') ?>">Оставить заявку</a></p>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr><th>№</th><th>Тип ремонта</th><th>Дата</th><th>Бюджет</th><th>Статус</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($requests, 0, 3) as $request): $badge = status_badge($request['status']); ?>
                                        <tr>
                                            <td>#<?= (int)$request['id'] ?></td>
                                            <td class="cell-main"><?= e($request['repair_type'] ?: 'Ремонт') ?><div class="cell-sub"><?= (int)$request['area'] ?> м²</div></td>
                                            <td><?= e(format_date($request['created_at'])) ?></td>
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

            <div class="admin-grid admin-grid--equal">
                <div class="panel">
                    <div class="panel__head"><h2>Профиль</h2></div>
                    <div class="panel__body">
                        <div class="contact-list" style="margin-top:0">
                            <div class="contact-item">
                                <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.4 0-8 2.2-8 5v3h16v-3c0-2.8-3.6-5-8-5z"/></svg></span>
                                <div><strong>Имя</strong><p><?= e($user['name']) ?></p></div>
                            </div>
                            <div class="contact-item">
                                <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M3 6.5A2.5 2.5 0 015.5 4h13A2.5 2.5 0 0121 6.5v11a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 17.5v-11z"/></svg></span>
                                <div><strong>E-mail</strong><p><?= e($user['email']) ?></p></div>
                            </div>
                            <div class="contact-item">
                                <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.2 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.2 1.1l-2.3 1.9z"/></svg></span>
                                <div><strong>Телефон</strong><p><?= e($user['phone'] ?: 'Не указан') ?></p></div>
                            </div>
                        </div>
                        <a class="btn btn--ghost btn--sm mt-3" href="<?= url('cabinet.php?tab=profile') ?>">Редактировать профиль</a>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel__head"><h2>Ваша выгода</h2></div>
                    <div class="panel__body">
                        <p class="text-muted" style="font-size:13.5px">Клиентам личного кабинета доступны персональные предложения и приоритетная запись на замер.</p>
                        <ul class="check-list mt-2">
                            <li>Скидка 5% при повторном заказе</li>
                            <li>Бесплатная консультация дизайнера</li>
                            <li>Приоритетная запись на замер</li>
                        </ul>
                        <a class="btn btn--primary btn--sm mt-3" href="<?= url('promos.php') ?>">Смотреть акции</a>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'requests'): ?>
            <div class="panel">
                <div class="panel__head">
                    <div>
                        <h2>История заявок</h2>
                        <p>Всего заявок: <?= $totalRequests ?> · общий бюджет: <?= $totalBudget ? format_price($totalBudget) : '—' ?></p>
                    </div>
                    <a class="btn btn--primary btn--sm" href="<?= url('request.php') ?>">Новая заявка</a>
                </div>
            </div>

            <?php if (!$requests): ?>
                <div class="panel"><div class="empty-state">
                    <div class="empty-state__icon">📋</div>
                    <h3>У вас пока нет заявок</h3>
                    <p>Расскажите о задаче — подготовим бесплатную смету.</p>
                    <p class="mt-3"><a class="btn btn--primary" href="<?= url('request.php') ?>">Оставить заявку</a></p>
                </div></div>
            <?php endif; ?>

            <?php foreach ($requests as $request): $badge = status_badge($request['status']); ?>
                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3>Заявка №<?= (int)$request['id'] ?> · <?= e($request['repair_type'] ?: 'Ремонт') ?></h3>
                            <p>Создана <?= e(format_date($request['created_at'])) ?><?= $request['area'] ? ' · ' . (int)$request['area'] . ' м²' : '' ?><?= $request['budget'] ? ' · бюджет ' . format_price((int)$request['budget']) : '' ?></p>
                        </div>
                        <span class="badge <?= e($badge['class']) ?>"><?= e($badge['label']) ?></span>
                    </div>
                    <div class="panel__body">
                        <?php if ($request['status'] !== 'cancelled'): ?>
                            <?php
                            $steps = [['new', 'Принята'], ['measurement', 'Замер и смета'], ['in_progress', 'В работе'], ['completed', 'Завершена']];
                            $currentIndex = array_search($request['status'], array_column($steps, 0), true);
                            $currentIndex = $currentIndex === false ? 0 : $currentIndex;
                            ?>
                            <div class="status-steps">
                                <?php foreach ($steps as $i => $step): ?>
                                    <div class="status-step<?= $i <= $currentIndex ? ' is-done' : '' ?>"><?= e($step[1]) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($request['address'] || $request['message']): ?>
                            <div class="form-grid mt-2">
                                <?php if ($request['address']): ?>
                                    <div><div class="contact-item"><span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 017 7c0 5.2-7 13-7 13S5 14.2 5 9a7 7 0 017-7zm0 4.5A2.5 2.5 0 109.5 9 2.5 2.5 0 0012 6.5z"/></svg></span><div><strong>Адрес</strong><p><?= e($request['address']) ?></p></div></div></div>
                                <?php endif; ?>
                                <?php if ($request['message']): ?>
                                    <div><div class="contact-item"><span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M4 4h16v12H8l-4 4V4z"/></svg></span><div><strong>Комментарий клиента</strong><p><?= e($request['message']) ?></p></div></div></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($request['manager_comment']): ?>
                            <div class="info-callout mt-3">
                                <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                <div><strong>Комментарий менеджера:</strong> <?= e($request['manager_comment']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($myReviews): ?>
                <div class="panel">
                    <div class="panel__head"><h3>Мои отзывы</h3></div>
                    <div class="panel__body panel__body--flush">
                        <div class="table-wrap">
                            <table class="table">
                                <thead><tr><th>Дата</th><th>Оценка</th><th>Услуга</th><th>Статус</th></tr></thead>
                                <tbody>
                                    <?php foreach ($myReviews as $review): $rb = review_status_badge($review['status']); ?>
                                        <tr>
                                            <td><?= e(format_date($review['created_at'])) ?></td>
                                            <td><?= render_stars((int)$review['rating']) ?></td>
                                            <td><?= e($review['service'] ?: '—') ?></td>
                                            <td><span class="badge <?= e($rb['class']) ?>"><?= e($rb['label']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($tab === 'profile'): ?>
            <div class="panel">
                <div class="panel__head">
                    <div><h2>Личные данные</h2><p>Эта информация используется при оформлении заявок</p></div>
                </div>
                <div class="panel__body">
                    <?php if ($profileErrors): ?>
                        <div class="flash flash--error mb-3"><?= e(reset($profileErrors)) ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= url('cabinet.php?tab=profile') ?>" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-grid">
                            <div class="field">
                                <label for="p-name">Имя и фамилия <span class="req">*</span></label>
                                <input type="text" id="p-name" name="name" value="<?= e($_POST['name'] ?? $user['name']) ?>" required>
                                <?php if (isset($profileErrors['name'])): ?><span class="field__error"><?= e($profileErrors['name']) ?></span><?php endif; ?>
                            </div>
                            <div class="field">
                                <label for="p-phone">Телефон <span class="req">*</span></label>
                                <input type="tel" id="p-phone" name="phone" value="<?= e($_POST['phone'] ?? $user['phone']) ?>" required>
                                <?php if (isset($profileErrors['phone'])): ?><span class="field__error"><?= e($profileErrors['phone']) ?></span><?php endif; ?>
                            </div>
                            <div class="field form-row--full">
                                <label for="p-email">E-mail <span class="req">*</span></label>
                                <input type="email" id="p-email" name="email" value="<?= e($_POST['email'] ?? $user['email']) ?>" required>
                                <?php if (isset($profileErrors['email'])): ?><span class="field__error"><?= e($profileErrors['email']) ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">Сохранить изменения</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="panel">
                <div class="panel__head"><h3>Служебная информация</h3></div>
                <div class="panel__body">
                    <div class="form-grid">
                        <div><div class="stat-card__label">Роль</div><span class="badge <?= e($role['class']) ?>"><?= e($role['label']) ?></span></div>
                        <div><div class="stat-card__label">Дата регистрации</div><strong><?= e(format_date($user['created_at'])) ?></strong></div>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'security'): ?>
            <div class="panel">
                <div class="panel__head">
                    <div><h2>Смена пароля</h2><p>Регулярно обновляйте пароль для безопасности аккаунта</p></div>
                </div>
                <div class="panel__body">
                    <?php if ($passwordErrors): ?>
                        <div class="flash flash--error mb-3"><?= e(reset($passwordErrors)) ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= url('cabinet.php?tab=security') ?>" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-grid">
                            <div class="field form-row--full">
                                <label for="cur-pass">Текущий пароль <span class="req">*</span></label>
                                <input type="password" id="cur-pass" name="current_password" autocomplete="current-password" required>
                                <?php if (isset($passwordErrors['current_password'])): ?><span class="field__error"><?= e($passwordErrors['current_password']) ?></span><?php endif; ?>
                            </div>
                            <div class="field">
                                <label for="new-pass">Новый пароль <span class="req">*</span></label>
                                <input type="password" id="new-pass" name="new_password" autocomplete="new-password" required>
                                <span class="field__hint">Минимум 8 символов, буквы и цифры</span>
                                <?php if (isset($passwordErrors['new_password'])): ?><span class="field__error"><?= e($passwordErrors['new_password']) ?></span><?php endif; ?>
                            </div>
                            <div class="field">
                                <label for="new-pass2">Повторите новый пароль <span class="req">*</span></label>
                                <input type="password" id="new-pass2" name="new_password_confirm" autocomplete="new-password" required>
                                <?php if (isset($passwordErrors['new_password_confirm'])): ?><span class="field__error"><?= e($passwordErrors['new_password_confirm']) ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">Изменить пароль</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="info-callout">
                <svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3zm-1.2 14.4l5.7-5.7-1.4-1.4-4.3 4.3-2-2-1.4 1.4 3.4 3.4z"/></svg>
                <div>Пароли хранятся в виде криптографических хешей (bcrypt). Мы никогда не видим ваш пароль и не передаём его третьим лицам.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
