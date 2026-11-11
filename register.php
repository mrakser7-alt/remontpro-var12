<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/forms.php';

if (is_logged_in()) {
    redirect('cabinet.php');
}

$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => ''];

if (is_post() && ($_POST['form'] ?? '') === 'register') {
    $old['name'] = trim((string)($_POST['name'] ?? ''));
    $old['email'] = trim((string)($_POST['email'] ?? ''));
    $old['phone'] = trim((string)($_POST['phone'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['password_confirm'] ?? '');

    if (!verify_csrf()) {
        $errors['csrf'] = 'Срок действия формы истёк. Обновите страницу.';
    }
    if ($spam = verify_antispam('register', 5)) {
        $errors['spam'] = $spam;
    }
    if (mb_strlen($old['name'], 'UTF-8') < 2) {
        $errors['name'] = 'Укажите имя (минимум 2 символа).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Укажите корректный e-mail.';
    }
    if (strlen(preg_replace('/\D/', '', $old['phone']) ?? '') < 10) {
        $errors['phone'] = 'Укажите корректный номер телефона.';
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-zА-Яа-я]/u', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Пароль должен содержать минимум 8 символов, буквы и цифры.';
    }
    if ($password !== $confirm) {
        $errors['password_confirm'] = 'Пароли не совпадают.';
    }
    if (empty($_POST['consent'])) {
        $errors['consent'] = 'Необходимо согласие на обработку персональных данных.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ((int)$stmt->fetchColumn() > 0) {
            $errors['email'] = 'Пользователь с таким e-mail уже зарегистрирован.';
        }
    }

    if (!$errors) {
        $colors = ['#e8a33d', '#4d9d8a', '#6f7fd6', '#c76b8e', '#8a9a5b', '#b8823d'];
        $stmt = db()->prepare(
            'INSERT INTO users (name, email, password_hash, phone, role, avatar_color, created_at)
             VALUES (?, ?, ?, ?, \'user\', ?, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            $old['name'],
            $old['email'],
            password_hash($password, PASSWORD_DEFAULT),
            $old['phone'],
            $colors[array_rand($colors)],
        ]);

        $userId = (int)db()->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        set_flash('success', 'Аккаунт создан! Добро пожаловать в РемонтПро.');
        redirect('cabinet.php');
    }
}

$pageTitle = 'Регистрация в личном кабинете';
$pageDescription = 'Создайте аккаунт РемонтПро, чтобы оставлять заявки на ремонт, следить за статусом работ и управлять профилем.';
$activeMenu = 'cabinet';
require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="auth-wrap">
        <div class="auth-side">
            <div>
                <a class="logo logo--light mb-4" href="<?= url('index.php') ?>" style="display:inline-flex">
                    <span class="logo__mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48"><rect width="48" height="48" rx="14" fill="#e8a33d"/><path d="M14 31.5V22l10-6.5 10 6.5v9.5" fill="none" stroke="#14181f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 33v-7h8v7" fill="none" stroke="#14181f" stroke-width="3" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="logo__text"><strong>Ремонт<span>Про</span></strong><small>квартиры и коттеджи</small></span>
                </a>
                <h2>Ремонт без сюрпризов</h2>
                <p>Зарегистрируйтесь, чтобы лично контролировать каждый этап работ и хранить все документы в одном месте.</p>
                <div class="auth-side__list">
                    <div><svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3z"/></svg> Пароли хранятся в виде хешей</div>
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Заявки и статусы — в реальном времени</div>
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Персональные акции для клиентов</div>
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Отзыв после завершения ремонта</div>
                </div>
            </div>
            <p style="font-size:12px;position:relative">Регистрация занимает меньше минуты.</p>
        </div>

        <div class="auth-form">
            <h1>Создать аккаунт</h1>
            <p>Уже есть аккаунт? <a href="<?= url('login.php') ?>" style="font-weight:700;text-decoration:underline;text-decoration-color:var(--accent)">Войти</a></p>

            <?php if ($errors): ?>
                <div class="flash flash--error mb-2"><?= e(reset($errors)) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('register.php') ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="form" value="register">
                <div class="field hp-field" aria-hidden="true">
                    <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                </div>
                <div class="field">
                    <label for="reg-name">Имя и фамилия <span class="req">*</span></label>
                    <input type="text" id="reg-name" name="name" value="<?= e($old['name']) ?>" placeholder="Александр Иванов" autocomplete="name" required>
                    <?php if (isset($errors['name'])): ?><span class="field__error"><?= e($errors['name']) ?></span><?php endif; ?>
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="reg-email">E-mail <span class="req">*</span></label>
                        <input type="email" id="reg-email" name="email" value="<?= e($old['email']) ?>" placeholder="you@mail.ru" autocomplete="email" required>
                        <?php if (isset($errors['email'])): ?><span class="field__error"><?= e($errors['email']) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="reg-phone">Телефон <span class="req">*</span></label>
                        <input type="tel" id="reg-phone" name="phone" value="<?= e($old['phone']) ?>" placeholder="+7 (___) ___-__-__" required>
                        <?php if (isset($errors['phone'])): ?><span class="field__error"><?= e($errors['phone']) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="reg-password">Пароль <span class="req">*</span></label>
                        <input type="password" id="reg-password" name="password" placeholder="Минимум 8 символов" autocomplete="new-password" required>
                        <?php if (isset($errors['password'])): ?><span class="field__error"><?= e($errors['password']) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="reg-password2">Повторите пароль <span class="req">*</span></label>
                        <input type="password" id="reg-password2" name="password_confirm" placeholder="Ещё раз" autocomplete="new-password" required>
                        <?php if (isset($errors['password_confirm'])): ?><span class="field__error"><?= e($errors['password_confirm']) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="field field--check">
                    <input type="checkbox" id="reg-consent" name="consent" value="1" <?= !empty($_POST['consent']) ? 'checked' : '' ?> required>
                    <label for="reg-consent">Согласен с <a href="<?= url('about.php') ?>#documents">политикой обработки персональных данных</a> и правилами сайта.</label>
                </div>
                <?php if (isset($errors['consent'])): ?><span class="field__error"><?= e($errors['consent']) ?></span><?php endif; ?>
                <button class="btn btn--primary btn--block" type="submit">Зарегистрироваться</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
