<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/art.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$stmt = db()->prepare('SELECT * FROM services WHERE slug = ? AND is_active = 1');
$stmt->execute([$slug]);
$service = $stmt->fetch();

if (!$service) {
    http_response_code(404);
    $pageTitle = 'Услуга не найдена';
    $activeMenu = 'services';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty-state"><div class="empty-state__icon">🔍</div><h3>Услуга не найдена</h3><p>Возможно, она была обновлена. Перейдите в каталог услуг.</p><p class="mt-3"><a class="btn btn--dark" href="' . url('services.php') . '">Все услуги</a></p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$related = db()->prepare('SELECT * FROM services WHERE is_active = 1 AND id != ? ORDER BY (category = ?) DESC, sort_order LIMIT 3');
$related->execute([$service['id'], $service['category']]);
$related = $related->fetchAll();

$otherProjects = db()->prepare('SELECT * FROM portfolio ORDER BY is_featured DESC, created_at DESC LIMIT 2');
$otherProjects->execute();
$otherProjects = $otherProjects->fetchAll();

$pageTitle = $service['title'] . ' — цена и сроки';
$pageDescription = $service['short_description'];
$activeMenu = 'services';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span>
            <a href="<?= url('services.php') ?>">Услуги</a><span class="sep">/</span>
            <span><?= e($service['title']) ?></span>
        </nav>
        <span class="eyebrow"><?= e($service['category']) ?></span>
        <h1><?= e($service['title']) ?></h1>
        <p><?= e($service['short_description']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container detail-grid">
        <div class="detail-body" data-reveal>
            <div class="about-photo mb-4"><?= room_art('warm', $service['icon']) ?></div>
            <h2>Описание услуги</h2>
            <p><?= nl2br(e($service['description'])) ?></p>

            <h2>Что входит в работу</h2>
            <ul class="check-list">
                <li>Бесплатный выезд инженера, замер и расчёт сметы</li>
                <li>Профессиональные материалы и инструмент</li>
                <li>Ежедневные фотоотчёты и контроль прораба</li>
                <li>Уборка помещения после завершения работ</li>
                <li>Гарантийное обслуживание и устранение замечаний</li>
            </ul>

            <h2>Этапы выполнения</h2>
            <div class="timeline mt-3">
                <div class="timeline-item2">
                    <span>Шаг 1</span>
                    <strong>Заявка и замер</strong>
                    <p>Согласуем время, инженер оценит объём работ и состояние поверхностей.</p>
                </div>
                <div class="timeline-item2">
                    <span>Шаг 2</span>
                    <strong>Смета и договор</strong>
                    <p>Подготовим подробную смету и график, зафиксируем стоимость в договоре.</p>
                </div>
                <div class="timeline-item2">
                    <span>Шаг 3</span>
                    <strong>Выполнение работ</strong>
                    <p>Бригада работает по графику, прораб контролирует качество и сроки.</p>
                </div>
                <div class="timeline-item2">
                    <span>Шаг 4</span>
                    <strong>Приёмка и гарантия</strong>
                    <p>Принимаете работу, получаете гарантийный талон и документы для налогового вычета.</p>
                </div>
            </div>
        </div>

        <aside class="aside-card aside-card--sticky" data-reveal>
            <div class="service-card__cat">Стоимость</div>
            <div class="price-tag">от <?= format_price((int)$service['price_from']) ?><br><small><?= e($service['price_unit']) ?></small></div>
            <ul class="aside-list">
                <li><span>Категория</span><strong><?= e($service['category']) ?></strong></li>
                <li><span>Срок выполнения</span><strong><?= e($service['duration']) ?></strong></li>
                <li><span>Выезд инженера</span><strong>Бесплатно</strong></li>
                <li><span>Гарантия</span><strong>3 года</strong></li>
            </ul>
            <a class="btn btn--primary btn--block" href="<?= url('request.php?type=' . urlencode($service['title'])) ?>">Заказать услугу</a>
            <a class="btn btn--ghost btn--block mt-1" href="tel:+74951204500">+7 (495) 120-45-00</a>
            <p class="form-note mt-2">Итоговая цена рассчитывается после бесплатного замера и зависит от площади и состояния объекта.</p>
        </aside>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Ещё услуги</span>
                <h2>С этим часто заказывают</h2>
            </div>
            <a class="btn btn--ghost" href="<?= url('services.php') ?>">Все услуги</a>
        </div>
        <div class="services-grid">
            <?php foreach ($related as $item): ?>
                <a class="service-card" href="<?= url('service.php?slug=' . urlencode($item['slug'])) ?>">
                    <span class="service-card__icon" style="background:<?= e($item['accent']) ?>22"><?= e($item['icon']) ?></span>
                    <span class="service-card__cat"><?= e($item['category']) ?></span>
                    <h3><?= e($item['title']) ?></h3>
                    <p><?= e($item['short_description']) ?></p>
                    <span class="service-card__meta">
                        <span class="service-card__price">от<strong><?= format_price((int)$item['price_from']) ?></strong><?= e($item['price_unit']) ?></span>
                        <span class="service-card__go"><svg viewBox="0 0 24 24"><path d="M13.2 5.4l6.4 6.6-6.4 6.6-1.4-1.4 4-4.1H4v-2h11.8l-4-4.1z"/></svg></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($otherProjects): ?>
            <div class="section-head mt-4">
                <div class="section-head__text">
                    <span class="eyebrow">Примеры работ</span>
                    <h2>Как это выглядит в жизни</h2>
                </div>
            </div>
            <div class="portfolio-grid">
                <?php foreach ($otherProjects as $project): ?>
                    <article class="project-card">
                        <a class="project-cover" href="<?= url('project.php?id=' . (int)$project['id']) ?>">
                            <?= room_art($project['tone'], $project['icon']) ?>
                            <span class="project-cover__cat"><?= e($project['category']) ?></span>
                            <span class="project-cover__area"><?= (int)$project['area'] ?> м²</span>
                        </a>
                        <div class="project-body">
                            <h3><a href="<?= url('project.php?id=' . (int)$project['id']) ?>"><?= e($project['title']) ?></a></h3>
                            <p><?= e(truncate_text($project['description'], 100)) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
