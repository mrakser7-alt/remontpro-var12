<?php
require_once __DIR__ . '/../includes/config.php';

$user = current_user();
if (!$user) {
    redirect('login.php?redirect=admin/users.php');
}
if ($user['role'] !== 'admin') {
    set_flash('error', 'Управление пользователями доступно только администратору.');
    redirect('admin/index.php');
}

$roles = ['admin' => 'Администратор', 'manager' => 'Менеджер', 'user' => 'Клиент'];
$errors = [];
$editId = (int)($_GET['edit'] ?? 0);
$isNew = isset($_GET['new']);
$item = ['id' => 0, 'name' => '', 'email' => '', 'phone' => '', 'role' => 'user', 'avatar_color' => '#e8a33d'];

if ($editId) {
    $stmt = db()->prepare('SELECT id, name, email, phone, role, avatar_color, created_at FROM users WHERE id = ?');
    $stmt->execute([$editId]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('error', 'Пользователь не найден.');
        redirect('admin/users.php');
    }
    $item = $found;
}

if (is_post() && verify_csrf()) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$user['id']) {
            set_flash('error', 'Нельзя удалить собственную учётную запись.');
        } else {
            $stmt = db()->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin' AND id != ?");
            $stmt->execute([$id]);
            $otherAdmins = (int)$stmt->fetchColumn();
            $stmt = db()->prepare('SELECT role FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $targetRole = $stmt->fetchColumn();
            if ($targetRole === 'admin' && $otherAdmins === 0) {
                set_flash('error', 'Нельзя удалить последнего администратора.');
            } else {
                db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
                set_flash('success', 'Пользователь удалён.');
            }
        }
        redirect('admin/users.php');
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'role' => (string)($_POST['role'] ?? 'user'),
            'avatar_color' => preg_match('/^#[0-9a-fA-F]{6}$/', (string)($_POST['avatar_color'] ?? '')) ? (string)$_POST['avatar_color'] : '#e8a33d',
        ];
        $password = (string)($_POST['password'] ?? '');

        if (mb_strlen($data['name'], 'UTF-8') < 2) {
            $errors['name'] = 'Укажите имя пользователя.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Укажите корректный e-mail.';
        }
        if (!isset($roles[$data['role']])) {
            $errors['role'] = 'Недопустимая роль.';
        }
        if ($id === (int)$user['id'] && $data['role'] !== 'admin') {
            $errors['role'] = 'Нельзя снять роль администратора с собственной учётной записи.';
        }
        if (!$id && $password === '') {
            $errors['password'] = 'Задайте пароль для нового пользователя.';
        }
        if ($password !== '' && (strlen($password) < 8 || !preg_match('/[A-Za-zА-Яа-я]/u', $password) || !preg_match('/\d/', $password))) {
            $errors['password'] = 'Пароль: минимум 8 символов, буквы и цифры.';
        }

        if (!$errors) {
            $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$data['email'], $id]);
            if ((int)$stmt->fetchColumn() > 0) {
                $errors['email'] = 'Пользователь с таким e-mail уже существует.';
            }
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE users SET name=?, email=?, phone=?, role=?, avatar_color=? WHERE id=?');
                $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['role'], $data['avatar_color'], $id]);
                if ($password !== '') {
                    db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
                }
                set_flash('success', 'Пользователь обновлён.');
            } else {
                $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, phone, role, avatar_color, created_at) VALUES (?,?,?,?,?,?,CURRENT_TIMESTAMP)');
                $stmt->execute([$data['name'], $data['email'], password_hash($password, PASSWORD_DEFAULT), $data['phone'], $data['role'], $data['avatar_color']]);
                set_flash('success', 'Пользователь создан.');
            }
            redirect('admin/users.php');
        }

        $item = array_merge($item, $data, ['id' => $id]);
        $editId = $id;
        $isNew = !$id;
    }
}

$list = db()->query('SELECT u.*, (SELECT COUNT(*) FROM requests r WHERE r.user_id = u.id) AS request_count FROM users u ORDER BY CASE u.role WHEN \'admin\' THEN 0 WHEN \'manager\' THEN 1 ELSE 2 END, u.created_at DESC')->fetchAll();

$adminTitle = ($editId || $isNew) ? ($editId ? 'Редактирование пользователя' : 'Новый пользователь') : 'Пользователи и роли';
$adminBreadcrumb = 'Главная / Администрирование / Пользователи';
$activeAdmin = 'users';
require __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($editId || $isNew): ?>
    <div class="flex-between mb-3 flex-wrap">
        <a class="link-arrow" href="<?= url('admin/users.php') ?>">← Назад к пользователям</a>
    </div>
    <?php if ($errors): ?><div class="flash flash--error mb-3">Исправьте ошибки в форме.</div><?php endif; ?>

    <div class="admin-grid">
        <div class="panel">
            <div class="panel__head"><div><h2><?= $editId ? 'Редактирование пользователя' : 'Создание пользователя' ?></h2><p>Права доступа определяются ролью</p></div></div>
            <div class="panel__body">
                <form method="post" action="<?= url('admin/users.php' . ($editId ? '?edit=' . $editId : '?new=1')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
                    <div class="form-grid">
                        <div class="field form-row--full">
                            <label for="u-name">Имя и фамилия <span class="req">*</span></label>
                            <input type="text" id="u-name" name="name" value="<?= e($item['name']) ?>" required>
                            <?php if (isset($errors['name'])): ?><span class="field__error"><?= e($errors['name']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="u-email">E-mail <span class="req">*</span></label>
                            <input type="email" id="u-email" name="email" value="<?= e($item['email']) ?>" required>
                            <?php if (isset($errors['email'])): ?><span class="field__error"><?= e($errors['email']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="u-phone">Телефон</label>
                            <input type="tel" id="u-phone" name="phone" value="<?= e($item['phone']) ?>">
                        </div>
                        <div class="field">
                            <label for="u-role">Роль <span class="req">*</span></label>
                            <select id="u-role" name="role">
                                <?php foreach ($roles as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $item['role'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['role'])): ?><span class="field__error"><?= e($errors['role']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="u-color">Цвет аватара</label>
                            <input type="color" id="u-color" name="avatar_color" value="<?= e($item['avatar_color']) ?>" style="height:48px;padding:5px">
                        </div>
                        <div class="field form-row--full">
                            <label for="u-password"><?= $editId ? 'Новый пароль (оставьте пустым, чтобы не менять)' : 'Пароль *' ?></label>
                            <input type="password" id="u-password" name="password" autocomplete="new-password" <?= $editId ? '' : 'required' ?>>
                            <span class="field__hint">Минимум 8 символов, буквы и цифры. Хранится в виде bcrypt-хеша.</span>
                            <?php if (isset($errors['password'])): ?><span class="field__error"><?= e($errors['password']) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit"><?= $editId ? 'Сохранить изменения' : 'Создать пользователя' ?></button>
                        <a class="btn btn--ghost" href="<?= url('admin/users.php') ?>">Отмена</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel__head"><h3>Права ролей</h3></div>
            <div class="panel__body">
                <div style="display:grid;gap:16px">
                    <div>
                        <span class="badge badge-admin">Администратор</span>
                        <p class="form-note mt-1">Полный доступ: контент, пользователи, заявки, модерация отзывов, настройки.</p>
                    </div>
                    <div>
                        <span class="badge badge-manager">Менеджер</span>
                        <p class="form-note mt-1">Обработка заявок и просмотр отзывов. Без редактирования контента и пользователей.</p>
                    </div>
                    <div>
                        <span class="badge badge-user">Клиент</span>
                        <p class="form-note mt-1">Личный кабинет, заявки, отзывы после завершения ремонта.</p>
                    </div>
                </div>
                <div class="info-callout mt-3" style="font-size:12px">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    <div>Нельзя удалить собственную учётную запись или снять с себя роль администратора.</div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="flex-between mb-3 flex-wrap">
        <p class="text-muted" style="margin:0;font-size:13.5px">Всего пользователей: <?= count($list) ?>. Роли определяют доступ к разделам сайта.</p>
        <a class="btn btn--primary" href="<?= url('admin/users.php?new=1') ?>">+ Создать пользователя</a>
    </div>
    <div class="panel">
        <div class="panel__body panel__body--flush">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Пользователь</th><th>Телефон</th><th>Роль</th><th>Заявки</th><th>Регистрация</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($list as $u): $rb = role_badge($u['role']); ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <span class="avatar avatar--sm" style="background:<?= e($u['avatar_color']) ?>"><?= e(initials($u['name'])) ?></span>
                                        <div><div class="cell-main"><?= e($u['name']) ?><?= (int)$u['id'] === (int)$user['id'] ? ' <span class="cell-sub">(вы)</span>' : '' ?></div><div class="cell-sub"><?= e($u['email']) ?></div></div>
                                    </div>
                                </td>
                                <td><?= e($u['phone'] ?: '—') ?></td>
                                <td><span class="badge <?= e($rb['class']) ?>"><?= e($rb['label']) ?></span></td>
                                <td><?= (int)$u['request_count'] ?></td>
                                <td><?= e(format_date($u['created_at'])) ?></td>
                                <td>
                                    <div class="cell-actions">
                                        <a class="icon-btn" href="<?= url('admin/users.php?edit=' . (int)$u['id']) ?>" title="Редактировать"><svg viewBox="0 0 24 24"><path d="M3 17.2V21h3.8L18 9.8 14.2 6 3 17.2zM20.7 7.1c.4-.4.4-1 0-1.4l-2.4-2.4c-.4-.4-1-.4-1.4 0l-1.9 1.9L18.8 9l1.9-1.9z"/></svg></a>
                                        <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                                            <form method="post" action="<?= url('admin/users.php') ?>" style="display:inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                <button class="icon-btn icon-btn--danger" type="submit" title="Удалить" data-confirm="Удалить пользователя <?= e($u['name']) ?>?"><svg viewBox="0 0 24 24"><path d="M6 7h12v14H6V7zm3-4h6l1 2h4v2H4V5h4l1-2zm1 7v8h2V10h-2zm4 0v8h2V10h-2z"/></svg></button>
                                            </form>
                                        <?php endif; ?>
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
