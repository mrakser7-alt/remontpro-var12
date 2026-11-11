<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Услуги — ремонт квартир и коттеджей';
$pageDescription = 'Все виды ремонтных работ: косметический и капитальный ремонт, дизайнерский ремонт, коттеджи, ванные, кухни и инженерные работы. Цены и сроки.';
$activeMenu = 'services';

$categories = cache_remember('service_categories', 120, function () {
    return db()->query('SELECT category, COUNT(*) AS cnt FROM services WHERE is_active = 1 GROUP BY category ORDER BY MIN(sort_order)')->fetchAll();
});

$selected = trim((string)($_GET['category'] ?? ''));
$validCategories = array_column($categories, 'category');
if ($selected !== '' && !in_array($selected, $validCategories, true)) {
    $selected = '';
}

$services = cache_remember('services_list_' . md5($selected), 120, function () use ($selected) {
    if ($selected !== '') {
        $stmt = db()->prepare('SELECT * FROM services WHERE is_active = 1 AND category = ? ORDER BY sort_order, id');
        $stmt->execute([$selected]);
        return $stmt->fetchAll();
    }
    return db()->query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
});

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>Услуги</span>
        </nav>
        <span class="eyebrow">Что мы делаем</span>
        <h1>Услуги и цены</h1>
        <p>Полный спектр ремонтных работ для квартир и загородных домов. Любую услугу можно заказать отдельно или в составе комплексного ремонта под ключ.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-bar">
            <a class="filter-chip<?= $selected === '' ? ' is-active' : '' ?>" href="<?= url('services.php') ?>">Все услуги <span><?= array_sum(array_column($categories, 'cnt')) ?></span></a>
            <?php foreach ($categories as $cat): ?>
                <a class="filter-chip<?= $selected === $cat['category'] ? ' is-active' : '' ?>" href="<?= url('services.php?category=' . urlencode($cat['category'])) ?>">
                    <?= e($cat['category']) ?> <span><?= (int)$cat['cnt'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!$services): ?>
            <div class="empty-state">
                <div class="empty-state__icon">🔍</div>
                <h3>По этому направлению пока нет услуг</h3>
                <p>Попробуйте выбрать другую категорию.</p>
            </div>
        <?php else: ?>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <a class="service-card" href="<?= url('service.php?slug=' . urlencode($service['slug'])) ?>" data-reveal>
                        <span class="service-card__icon" style="background:<?= e($service['accent']) ?>22"><?= e($service['icon']) ?></span>
                        <span class="service-card__cat"><?= e($service['category']) ?></span>
                        <h3><?= e($service['title']) ?></h3>
                        <p><?= e($service['short_description']) ?></p>
                        <span class="service-card__meta">
                            <span class="service-card__price">от<strong><?= format_price((int)$service['price_from']) ?></strong><?= e($service['price_unit']) ?></span>
                            <span class="service-card__go"><svg viewBox="0 0 24 24"><path d="M13.2 5.4l6.4 6.6-6.4 6.6-1.4-1.4 4-4.1H4v-2h11.8l-4-4.1z"/></svg></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Как считается цена</span>
                <h2>Прозрачное ценообразование</h2>
                <p>Итоговая смета зависит от площади, состояния объекта и выбранных материалов. После бесплатного замера вы получаете точный расчёт.</p>
            </div>
        </div>
        <div class="values-grid">
            <div class="value-card">
                <span class="service-card__icon">📐</span>
                <h3>Площадь и геометрия</h3>
                <p>Чем сложнее планировка и больше мокрых зон, тем выше трудозатраты. Все коэффициенты видны в смете.</p>
            </div>
            <div class="value-card">
                <span class="service-card__icon">🧱</span>
                <h3>Материалы</h3>
                <p>Работаем с материалами из бюджета, среднего и премиального сегментов. Закупаем по дилерским ценам.</p>
            </div>
            <div class="value-card">
                <span class="service-card__icon">⚙️</span>
                <h3>Инженерия</h3>
                <p>Замена электрики, сантехники и вентиляции рассчитывается отдельно — до начала работ.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="promo-banner">
            <div>
                <span class="eyebrow" style="color:#7a4a05">Бесплатный замер</span>
                <h2>Не знаете, с чего начать?</h2>
                <p>Приедем на объект, сделаем замеры и подготовим смету в течение 24 часов. Это бесплатно и ни к чему не обязывает.</p>
            </div>
            <a class="btn btn--dark btn--lg" href="<?= url('request.php') ?>">Заказать замер</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
