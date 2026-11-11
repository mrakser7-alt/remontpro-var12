<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/forms.php';

if (is_logged_in()) {
    redirect('cabinet.php');
}

$errors = [];
$email = '';

$redirectTarget = (string)($_GET['redirect'] ?? '');
if ($redirectTarget !== '' && (str_contains($redirectTarget, ':') || str_starts_with($redirectTarget, '//'))) {
    $redirectTarget = '';
}

if (is_post() && ($_POST['form'] ?? '') === 'login') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!verify_csrf()) {
        $errors['csrf'] = 'Срок действия формы истёк. Обновите страницу.';
    }
    if ($spam = verify_antispam('login', 2)) {
        $errors['spam'] = $spam;
    }
    if ($email === '' || $password === '') {
        $errors['auth'] = 'Введите e-mail и пароль.';
    }

    // Ограничение попыток входа
    $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'since' => time()];
    if ($attempts['count'] >= 5 && (time() - $attempts['since']) < 300) {
        $errors['auth'] = 'Слишком много попыток входа. Попробуйте через 5 минут.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $found = $stmt->fetch();

        if ($found && password_verify($password, $found['password_hash'])) {
            unset($_SESSION['login_attempts']);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$found['id'];
            set_flash('success', 'Добро пожаловать, ' . $found['name'] . '!');

            if ($redirectTarget !== '') {
                redirect($redirectTarget);
            }
            if (in_array($found['role'], ['admin', 'manager'], true)) {
                redirect('admin/index.php');
            }
            redirect('cabinet.php');
        }

        $attempts['count']++;
        if ($attempts['count'] === 1) {
            $attempts['since'] = time();
        }
        $_SESSION['login_attempts'] = $attempts;
        $errors['auth'] = 'Неверный e-mail или пароль.';
    }
}

$pageTitle = 'Вход в личный кабинет';
$pageDescription = 'Авторизация в личном кабинете РемонтПро: история заявок, статусы ремонта и управление профилем.';
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
                <h2>Контролируйте ремонт онлайн</h2>
                <p>В личном кабинете вы видите статус заявок, комментарии прораба и историю работ — без звонков и лишней переписки.</p>
                <div class="auth-side__list">
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> История заявок и статусы выполнения</div>
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Редактирование профиля и контактов</div>
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Отзывы после завершения ремонта</div>
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Безопасное хранение данных</div>
                </div>
            </div>
            <p style="font-size:12px;position:relative">© <?= date('Y') ?> РемонтПро. Все права защищены.</p>
        </div>

        <div class="auth-form">
            <h1>Вход в кабинет</h1>
            <p>Введите данные, которые указали при регистрации.</p>

            <?php if ($errors): ?>
                <div class="flash flash--error mb-2"><?= e(reset($errors)) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('login.php' . ($redirectTarget ? '?redirect=' . urlencode($redirectTarget) : '')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="form" value="login">
                <div class="field hp-field" aria-hidden="true">
                    <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                </div>
                <div class="field">
                    <label for="login-email">E-mail</label>
                    <input type="email" id="login-email" name="email" value="<?= e($email) ?>" placeholder="you@mail.ru" autocomplete="email" required>
                </div>
                <div class="field">
                    <label for="login-password">Пароль</label>
                    <input type="password" id="login-password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                </div>
                <button class="btn btn--primary btn--block" type="submit">Войти</button>
            </form>

            <p class="form-footer">Нет аккаунта? <a href="<?= url('register.php') ?>">Зарегистрироваться</a></p>

            <div class="demo-creds">
                <strong>Демонстрационные аккаунты:</strong><br>
                Администратор — <code>admin@remontpro.ru</code> / <code>Admin123!</code><br>
                Менеджер — <code>manager@remontpro.ru</code> / <code>Manager123!</code><br>
                Клиент — <code>client@remontpro.ru</code> / <code>Client123!</code>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
