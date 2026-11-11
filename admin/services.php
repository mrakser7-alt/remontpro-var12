<?php
require_once __DIR__ . '/../includes/config.php';

$user = current_user();
if (!$user) {
    redirect('login.php?redirect=admin/services.php');
}
if ($user['role'] !== 'admin') {
    set_flash('error', 'Управление услугами доступно только администратору.');
    redirect('admin/index.php');
}

$errors = [];
$editId = (int)($_GET['edit'] ?? 0);
$isNew = isset($_GET['new']);
$item = [
    'id' => 0, 'title' => '', 'slug' => '', 'category' => 'Косметический ремонт', 'icon' => '🔨',
    'accent' => '#e8a33d', 'short_description' => '', 'description' => '', 'price_from' => '',
    'price_unit' => '₽/м²', 'duration' => '', 'is_active' => 1, 'sort_order' => 0,
];

if ($editId) {
    $stmt = db()->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([$editId]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('error', 'Услуга не найдена.');
        redirect('admin/services.php');
    }
    $item = $found;
} elseif (!$isNew) {
    $isNew = false;
}

if (is_post() && verify_csrf()) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
        cache_clear();
        set_flash('success', 'Услуга удалена.');
        redirect('admin/services.php');
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'title' => trim((string)($_POST['title'] ?? '')),
            'category' => trim((string)($_POST['category'] ?? '')),
            'icon' => trim((string)($_POST['icon'] ?? '🔨')) ?: '🔨',
            'accent' => preg_match('/^#[0-9a-fA-F]{6}$/', (string)($_POST['accent'] ?? '')) ? (string)$_POST['accent'] : '#e8a33d',
            'short_description' => trim((string)($_POST['short_description'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'price_from' => int_or_zero($_POST['price_from'] ?? 0),
            'price_unit' => trim((string)($_POST['price_unit'] ?? '₽/м²')),
            'duration' => trim((string)($_POST['duration'] ?? '')),
            'sort_order' => int_or_zero($_POST['sort_order'] ?? 0),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'slug' => trim((string)($_POST['slug'] ?? '')),
        ];

        if (mb_strlen($data['title'], 'UTF-8') < 3) {
            $errors['title'] = 'Название должно содержать минимум 3 символа.';
        }
        if ($data['category'] === '') {
            $errors['category'] = 'Укажите категорию.';
        }
        if ($data['short_description'] === '') {
            $errors['short_description'] = 'Добавьте краткое описание.';
        }
        if ($data['description'] === '') {
            $errors['description'] = 'Добавьте полное описание.';
        }

        if (!$errors) {
            if ($data['slug'] === '') {
                $data['slug'] = unique_slug(db(), 'services', make_slug($data['title']), $id ?: null);
            } else {
                $data['slug'] = unique_slug(db(), 'services', make_slug($data['slug']), $id ?: null);
            }

            if ($id) {
                $stmt = db()->prepare('UPDATE services SET slug=?, title=?, category=?, icon=?, accent=?, short_description=?, description=?, price_from=?, price_unit=?, duration=?, sort_order=?, is_active=? WHERE id=?');
                $stmt->execute([$data['slug'], $data['title'], $data['category'], $data['icon'], $data['accent'], $data['short_description'], $data['description'], $data['price_from'], $data['price_unit'], $data['duration'], $data['sort_order'], $data['is_active'], $id]);
            } else {
                $stmt = db()->prepare('INSERT INTO services (slug, title, category, icon, accent, short_description, description, price_from, price_unit, duration, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$data['slug'], $data['title'], $data['category'], $data['icon'], $data['accent'], $data['short_description'], $data['description'], $data['price_from'], $data['price_unit'], $data['duration'], $data['sort_order'], $data['is_active']]);
            }
            cache_clear();
            set_flash('success', $id ? 'Услуга обновлена.' : 'Услуга добавлена.');
            redirect('admin/services.php');
        }

        $item = array_merge($item, $data, ['id' => $id]);
        $editId = $id;
        $isNew = !$id;
    }
}

$services = db()->query('SELECT * FROM services ORDER BY sort_order, id')->fetchAll();

$adminTitle = ($editId || $isNew) ? ($editId ? 'Редактирование услуги' : 'Новая услуга') : 'Услуги';
$adminBreadcrumb = 'Главная / Контент / Услуги';
$activeAdmin = 'services';
require __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($editId || $isNew): ?>
    <div class="flex-between mb-3 flex-wrap">
        <a class="link-arrow" href="<?= url('admin/services.php') ?>">← Назад к списку услуг</a>
        <?php if ($editId): ?>
            <a class="btn btn--ghost btn--sm" href="<?= url('service.php?slug=' . urlencode($item['slug'])) ?>" target="_blank">Посмотреть на сайте</a>
        <?php endif; ?>
    </div>

    <?php if ($errors): ?><div class="flash flash--error mb-3">Исправьте ошибки в форме.</div><?php endif; ?>

    <div class="panel">
        <div class="panel__head"><div><h2><?= $editId ? 'Редактирование услуги' : 'Добавление услуги' ?></h2><p>Заполните информацию, которая отображается на сайте</p></div></div>
        <div class="panel__body">
            <form method="post" action="<?= url('admin/services.php' . ($editId ? '?edit=' . $editId : '?new=1')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
                <div class="form-grid">
                    <div class="field form-row--full">
                        <label for="s-title">Название <span class="req">*</span></label>
                        <input type="text" id="s-title" name="title" value="<?= e($item['title']) ?>" required>
                        <?php if (isset($errors['title'])): ?><span class="field__error"><?= e($errors['title']) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="s-category">Категория <span class="req">*</span></label>
                        <input type="text" id="s-category" name="category" value="<?= e($item['category']) ?>" list="cat-list" required>
                        <datalist id="cat-list">
                            <?php foreach (repair_categories() as $cat): ?><option value="<?= e($cat) ?>"><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="field">
                        <label for="s-icon">Иконка (эмодзи)</label>
                        <input type="text" id="s-icon" name="icon" value="<?= e($item['icon']) ?>" maxlength="8">
                    </div>
                    <div class="field">
                        <label for="s-price">Цена от, ₽</label>
                        <input type="number" id="s-price" name="price_from" min="0" step="100" value="<?= e((string)$item['price_from']) ?>">
                    </div>
                    <div class="field">
                        <label for="s-unit">Единица цены</label>
                        <input type="text" id="s-unit" name="price_unit" value="<?= e($item['price_unit']) ?>">
                    </div>
                    <div class="field">
                        <label for="s-duration">Срок выполнения</label>
                        <input type="text" id="s-duration" name="duration" value="<?= e($item['duration']) ?>" placeholder="от 3 недель">
                    </div>
                    <div class="field">
                        <label for="s-accent">Цвет акцента</label>
                        <input type="color" id="s-accent" name="accent" value="<?= e($item['accent']) ?>" style="height:48px;padding:5px">
                    </div>
                    <div class="field">
                        <label for="s-sort">Порядок сортировки</label>
                        <input type="number" id="s-sort" name="sort_order" value="<?= (int)$item['sort_order'] ?>">
                    </div>
                    <div class="field">
                        <label for="s-slug">Slug (URL)</label>
                        <input type="text" id="s-slug" name="slug" value="<?= e($item['slug']) ?>" placeholder="генерируется автоматически">
                    </div>
                    <div class="field field--check form-row--full">
                        <input type="checkbox" id="s-active" name="is_active" value="1" <?= (int)$item['is_active'] === 1 ? 'checked' : '' ?>>
                        <label for="s-active">Услуга активна и отображается на сайте</label>
                    </div>
                    <div class="field form-row--full">
                        <label for="s-short">Краткое описание <span class="req">*</span></label>
                        <textarea id="s-short" name="short_description" rows="2" maxlength="300" required><?= e($item['short_description']) ?></textarea>
                        <?php if (isset($errors['short_description'])): ?><span class="field__error"><?= e($errors['short_description']) ?></span><?php endif; ?>
                    </div>
                    <div class="field form-row--full">
                        <label for="s-desc">Полное описание <span class="req">*</span></label>
                        <textarea id="s-desc" name="description" rows="8" required><?= e($item['description']) ?></textarea>
                        <?php if (isset($errors['description'])): ?><span class="field__error"><?= e($errors['description']) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn--primary" type="submit"><?= $editId ? 'Сохранить изменения' : 'Добавить услугу' ?></button>
                    <a class="btn btn--ghost" href="<?= url('admin/services.php') ?>">Отмена</a>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <div class="flex-between mb-3 flex-wrap">
        <p class="text-muted" style="margin:0;font-size:13.5px">Всего услуг: <?= count($services) ?>. Неактивные услуги скрыты на сайте.</p>
        <a class="btn btn--primary" href="<?= url('admin/services.php?new=1') ?>">+ Добавить услугу</a>
    </div>
    <div class="panel">
        <div class="panel__body panel__body--flush">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Услуга</th><th>Категория</th><th>Цена</th><th>Срок</th><th>Статус</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($services as $s): ?>
                            <tr>
                                <td class="cell-main"><?= e($s['icon']) ?> <?= e($s['title']) ?><div class="cell-sub">/<?= e($s['slug']) ?></div></td>
                                <td><?= e($s['category']) ?></td>
                                <td><?= format_price((int)$s['price_from']) ?><div class="cell-sub"><?= e($s['price_unit']) ?></div></td>
                                <td><?= e($s['duration'] ?: '—') ?></td>
                                <td><span class="badge <?= (int)$s['is_active'] ? 'status-completed' : 'status-cancelled' ?>"><?= (int)$s['is_active'] ? 'Активна' : 'Скрыта' ?></span></td>
                                <td>
                                    <div class="cell-actions">
                                        <a class="icon-btn" href="<?= url('admin/services.php?edit=' . (int)$s['id']) ?>" title="Редактировать"><svg viewBox="0 0 24 24"><path d="M3 17.2V21h3.8L18 9.8 14.2 6 3 17.2zM20.7 7.1c.4-.4.4-1 0-1.4l-2.4-2.4c-.4-.4-1-.4-1.4 0l-1.9 1.9L18.8 9l1.9-1.9z"/></svg></a>
                                        <form method="post" action="<?= url('admin/services.php') ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                            <button class="icon-btn icon-btn--danger" type="submit" title="Удалить" data-confirm="Удалить услугу «<?= e($s['title']) ?>»?"><svg viewBox="0 0 24 24"><path d="M6 7h12v14H6V7zm3-4h6l1 2h4v2H4V5h4l1-2zm1 7v8h2V10h-2zm4 0v8h2V10h-2z"/></svg></button>
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
