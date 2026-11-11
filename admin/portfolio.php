<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/art.php';

$user = current_user();
if (!$user) {
    redirect('login.php?redirect=admin/portfolio.php');
}
if ($user['role'] !== 'admin') {
    set_flash('error', 'Управление портфолио доступно только администратору.');
    redirect('admin/index.php');
}

$errors = [];
$editId = (int)($_GET['edit'] ?? 0);
$isNew = isset($_GET['new']);
$item = [
    'id' => 0, 'title' => '', 'category' => 'Квартиры', 'location' => '', 'area' => '',
    'duration' => '', 'budget' => '', 'description' => '', 'tone' => 'warm', 'icon' => '🛋️', 'is_featured' => 0,
];

if ($editId) {
    $stmt = db()->prepare('SELECT * FROM portfolio WHERE id = ?');
    $stmt->execute([$editId]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('error', 'Проект не найден.');
        redirect('admin/portfolio.php');
    }
    $item = $found;
}

$tones = ['warm' => 'Тёплый беж', 'sage' => 'Шалфей', 'cool' => 'Прохладный', 'clay' => 'Терракота', 'sand' => 'Песочный', 'night' => 'Тёмный'];

if (is_post() && verify_csrf()) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete') {
        db()->prepare('DELETE FROM portfolio WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        cache_clear();
        set_flash('success', 'Проект удалён из портфолио.');
        redirect('admin/portfolio.php');
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'title' => trim((string)($_POST['title'] ?? '')),
            'category' => (string)($_POST['category'] ?? 'Квартиры'),
            'location' => trim((string)($_POST['location'] ?? '')),
            'area' => int_or_zero($_POST['area'] ?? 0),
            'duration' => trim((string)($_POST['duration'] ?? '')),
            'budget' => int_or_zero($_POST['budget'] ?? 0),
            'description' => trim((string)($_POST['description'] ?? '')),
            'tone' => array_key_exists((string)($_POST['tone'] ?? ''), $tones) ? (string)$_POST['tone'] : 'warm',
            'icon' => trim((string)($_POST['icon'] ?? '🛋️')) ?: '🛋️',
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
        ];

        if (mb_strlen($data['title'], 'UTF-8') < 3) {
            $errors['title'] = 'Укажите название проекта.';
        }
        if (!in_array($data['category'], portfolio_categories(), true)) {
            $errors['category'] = 'Выберите категорию.';
        }
        if ($data['area'] <= 0) {
            $errors['area'] = 'Укажите площадь объекта.';
        }
        if ($data['description'] === '') {
            $errors['description'] = 'Добавьте описание проекта.';
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE portfolio SET title=?, category=?, location=?, area=?, duration=?, budget=?, description=?, tone=?, icon=?, is_featured=? WHERE id=?');
                $stmt->execute([$data['title'], $data['category'], $data['location'], $data['area'], $data['duration'], $data['budget'], $data['description'], $data['tone'], $data['icon'], $data['is_featured'], $id]);
            } else {
                $stmt = db()->prepare('INSERT INTO portfolio (title, category, location, area, duration, budget, description, tone, icon, is_featured, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)');
                $stmt->execute([$data['title'], $data['category'], $data['location'], $data['area'], $data['duration'], $data['budget'], $data['description'], $data['tone'], $data['icon'], $data['is_featured']]);
            }
            cache_clear();
            set_flash('success', $id ? 'Проект обновлён.' : 'Проект добавлен в портфолио.');
            redirect('admin/portfolio.php');
        }

        $item = array_merge($item, $data, ['id' => $id]);
        $editId = $id;
        $isNew = !$id;
    }
}

$projects = db()->query('SELECT * FROM portfolio ORDER BY is_featured DESC, created_at DESC')->fetchAll();

$adminTitle = ($editId || $isNew) ? ($editId ? 'Редактирование проекта' : 'Новый проект') : 'Портфолио';
$adminBreadcrumb = 'Главная / Контент / Портфолио';
$activeAdmin = 'portfolio';
require __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($editId || $isNew): ?>
    <div class="flex-between mb-3 flex-wrap">
        <a class="link-arrow" href="<?= url('admin/portfolio.php') ?>">← Назад к портфолио</a>
        <?php if ($editId): ?>
            <a class="btn btn--ghost btn--sm" href="<?= url('project.php?id=' . (int)$item['id']) ?>" target="_blank">Посмотреть на сайте</a>
        <?php endif; ?>
    </div>

    <?php if ($errors): ?><div class="flash flash--error mb-3">Исправьте ошибки в форме.</div><?php endif; ?>

    <div class="panel">
        <div class="panel__head"><div><h2><?= $editId ? 'Редактирование проекта' : 'Добавление проекта' ?></h2><p>Превью обложки формируется автоматически по выбранному тону</p></div></div>
        <div class="panel__body">
            <div class="form-grid" style="align-items:start">
                <form method="post" action="<?= url('admin/portfolio.php' . ($editId ? '?edit=' . $editId : '?new=1')) ?>" class="form-row--full" style="grid-column:1/-1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
                    <div class="form-grid">
                        <div class="field form-row--full">
                            <label for="p-title">Название проекта <span class="req">*</span></label>
                            <input type="text" id="p-title" name="title" value="<?= e($item['title']) ?>" required>
                            <?php if (isset($errors['title'])): ?><span class="field__error"><?= e($errors['title']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="p-category">Категория</label>
                            <select id="p-category" name="category">
                                <?php foreach (portfolio_categories() as $cat): ?>
                                    <option value="<?= e($cat) ?>" <?= $item['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="p-location">Локация</label>
                            <input type="text" id="p-location" name="location" value="<?= e($item['location']) ?>" placeholder="Москва, район">
                        </div>
                        <div class="field">
                            <label for="p-area">Площадь, м² <span class="req">*</span></label>
                            <input type="number" id="p-area" name="area" min="1" value="<?= e((string)$item['area']) ?>" required>
                            <?php if (isset($errors['area'])): ?><span class="field__error"><?= e($errors['area']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="p-duration">Срок</label>
                            <input type="text" id="p-duration" name="duration" value="<?= e($item['duration']) ?>" placeholder="11 недель">
                        </div>
                        <div class="field">
                            <label for="p-budget">Бюджет, ₽</label>
                            <input type="number" id="p-budget" name="budget" min="0" step="1000" value="<?= e((string)$item['budget']) ?>">
                        </div>
                        <div class="field">
                            <label for="p-tone">Цветовая тема обложки</label>
                            <select id="p-tone" name="tone">
                                <?php foreach ($tones as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $item['tone'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="p-icon">Иконка (эмодзи)</label>
                            <input type="text" id="p-icon" name="icon" value="<?= e($item['icon']) ?>" maxlength="8">
                        </div>
                        <div class="field field--check form-row--full">
                            <input type="checkbox" id="p-featured" name="is_featured" value="1" <?= (int)$item['is_featured'] === 1 ? 'checked' : '' ?>>
                            <label for="p-featured">Показывать на главной странице</label>
                        </div>
                        <div class="field form-row--full">
                            <label for="p-desc">Описание проекта <span class="req">*</span></label>
                            <textarea id="p-desc" name="description" rows="7" required><?= e($item['description']) ?></textarea>
                            <?php if (isset($errors['description'])): ?><span class="field__error"><?= e($errors['description']) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit"><?= $editId ? 'Сохранить изменения' : 'Добавить проект' ?></button>
                        <a class="btn btn--ghost" href="<?= url('admin/portfolio.php') ?>">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="panel mt-3">
        <div class="panel__head"><h3>Предпросмотр обложки</h3></div>
        <div class="panel__body">
            <div style="max-width:420px" class="project-cover"><?= room_art($item['tone'], $item['icon']) ?><span class="project-cover__cat"><?= e($item['category']) ?></span><span class="project-cover__area"><?= (int)$item['area'] ?> м²</span></div>
        </div>
    </div>

<?php else: ?>
    <div class="flex-between mb-3 flex-wrap">
        <p class="text-muted" style="margin:0;font-size:13.5px">Всего проектов: <?= count($projects) ?>. Отмеченные звёздочкой показываются на главной.</p>
        <a class="btn btn--primary" href="<?= url('admin/portfolio.php?new=1') ?>">+ Добавить проект</a>
    </div>
    <div class="panel">
        <div class="panel__body panel__body--flush">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Проект</th><th>Категория</th><th>Площадь</th><th>Бюджет</th><th>Главная</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:12px">
                                        <span style="width:64px;height:44px;border-radius:8px;overflow:hidden;display:block;flex-shrink:0"><?= room_art($p['tone'], $p['icon']) ?></span>
                                        <div><div class="cell-main"><?= e($p['title']) ?></div><div class="cell-sub"><?= e($p['location']) ?> · <?= e(format_date($p['created_at'])) ?></div></div>
                                    </div>
                                </td>
                                <td><?= e($p['category']) ?></td>
                                <td><?= (int)$p['area'] ?> м²</td>
                                <td><?= $p['budget'] ? format_price((int)$p['budget']) : '—' ?></td>
                                <td><?= (int)$p['is_featured'] ? '<span class="badge status-completed">Да</span>' : '<span class="badge badge--plain" style="background:var(--bg)">Нет</span>' ?></td>
                                <td>
                                    <div class="cell-actions">
                                        <a class="icon-btn" href="<?= url('admin/portfolio.php?edit=' . (int)$p['id']) ?>" title="Редактировать"><svg viewBox="0 0 24 24"><path d="M3 17.2V21h3.8L18 9.8 14.2 6 3 17.2zM20.7 7.1c.4-.4.4-1 0-1.4l-2.4-2.4c-.4-.4-1-.4-1.4 0l-1.9 1.9L18.8 9l1.9-1.9z"/></svg></a>
                                        <form method="post" action="<?= url('admin/portfolio.php') ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button class="icon-btn icon-btn--danger" type="submit" title="Удалить" data-confirm="Удалить проект «<?= e($p['title']) ?>»?"><svg viewBox="0 0 24 24"><path d="M6 7h12v14H6V7zm3-4h6l1 2h4v2H4V5h4l1-2zm1 7v8h2V10h-2zm4 0v8h2V10h-2z"/></svg></button>
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
