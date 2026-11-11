<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/art.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM portfolio WHERE id = ?');
$stmt->execute([$id]);
$project = $stmt->fetch();

$pageTitle = $project ? $project['title'] : 'Проект не найден';
$activeMenu = 'portfolio';

if (!$project) {
    http_response_code(404);
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty-state"><div class="empty-state__icon">🖼️</div><h3>Проект не найден</h3><p>Возможно, он был удалён. Перейдите в портфолио.</p><p class="mt-3"><a class="btn btn--dark" href="' . url('portfolio.php') . '">Все проекты</a></p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$relatedStmt = db()->prepare('SELECT * FROM portfolio WHERE id != ? ORDER BY (category = ?) DESC, is_featured DESC LIMIT 3');
$relatedStmt->execute([$project['id'], $project['category']]);
$related = $relatedStmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span>
            <a href="<?= url('portfolio.php') ?>">Портфолио</a><span class="sep">/</span>
            <span><?= e($project['title']) ?></span>
        </nav>
        <span class="eyebrow"><?= e($project['category']) ?> · <?= e($project['location']) ?></span>
        <h1><?= e($project['title']) ?></h1>
        <p><?= (int)$project['area'] ?> м² · срок <?= e($project['duration']) ?> · бюджет <?= format_price((int)$project['budget']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="project-cover mb-4" style="border-radius:var(--radius-l);min-height:340px" data-reveal>
            <?= room_art($project['tone'], $project['icon']) ?>
            <span class="project-cover__cat"><?= e($project['category']) ?></span>
            <span class="project-cover__area"><?= (int)$project['area'] ?> м²</span>
        </div>

        <div class="detail-grid">
            <div class="detail-body" data-reveal>
                <h2>О проекте</h2>
                <p><?= nl2br(e($project['description'])) ?></p>

                <h2>Что сделали</h2>
                <ul class="check-list">
                    <li>Разработали планировочное решение и согласовали его с клиентом</li>
                    <li>Выполнили демонтаж и подготовку оснований</li>
                    <li>Проложили инженерные сети: электрика, сантехника, вентиляция</li>
                    <li>Выполнили чистовую отделку и монтаж оборудования</li>
                    <li>Провели финальную уборку и сдали объект по акту</li>
                </ul>

                <h2>Галерея проекта</h2>
                <div class="portfolio-grid" style="gap:16px;margin-top:18px">
                    <div class="project-cover" style="border-radius:14px"><?= room_art($project['tone'], '🪟') ?></div>
                    <div class="project-cover" style="border-radius:14px"><?= room_art($project['tone'], '🛏️') ?></div>
                    <div class="project-cover" style="border-radius:14px"><?= room_art($project['tone'], '💡') ?></div>
                    <div class="project-cover" style="border-radius:14px"><?= room_art($project['tone'], '🪴') ?></div>
                </div>
            </div>

            <aside class="aside-card aside-card--sticky" data-reveal>
                <div class="service-card__cat">Параметры проекта</div>
                <ul class="aside-list">
                    <li><span>Тип объекта</span><strong><?= e($project['category']) ?></strong></li>
                    <li><span>Локация</span><strong><?= e($project['location']) ?></strong></li>
                    <li><span>Площадь</span><strong><?= (int)$project['area'] ?> м²</strong></li>
                    <li><span>Срок ремонта</span><strong><?= e($project['duration']) ?></strong></li>
                    <li><span>Бюджет</span><strong><?= format_price((int)$project['budget']) ?></strong></li>
                    <li><span>Гарантия</span><strong>3 года</strong></li>
                </ul>
                <a class="btn btn--primary btn--block" href="<?= url('request.php?type=' . urlencode($project['category'] === 'Коттеджи' ? 'Ремонт коттеджа под ключ' : 'Капитальный ремонт квартиры')) ?>">Хочу похожий проект</a>
                <a class="btn btn--ghost btn--block mt-1" href="<?= url('portfolio.php') ?>">Смотреть ещё работы</a>
                <p class="form-note mt-2">Стоимость указана по факту завершения проекта и включает работы и материалы.</p>
            </aside>
        </div>
    </div>
</section>

<?php if ($related): ?>
<section class="section section--paper">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Похожие проекты</span>
                <h2>Ещё из нашего портфолио</h2>
            </div>
            <a class="btn btn--ghost" href="<?= url('portfolio.php') ?>">Все проекты</a>
        </div>
        <div class="portfolio-grid portfolio-grid--3">
            <?php foreach ($related as $item): ?>
                <article class="project-card">
                    <a class="project-cover" href="<?= url('project.php?id=' . (int)$item['id']) ?>">
                        <?= room_art($item['tone'], $item['icon']) ?>
                        <span class="project-cover__cat"><?= e($item['category']) ?></span>
                        <span class="project-cover__area"><?= (int)$item['area'] ?> м²</span>
                    </a>
                    <div class="project-body">
                        <h3><a href="<?= url('project.php?id=' . (int)$item['id']) ?>"><?= e($item['title']) ?></a></h3>
                        <div class="project-meta">
                            <span><strong><?= e($item['duration']) ?></strong>срок</span>
                            <span><strong><?= format_price((int)$item['budget']) ?></strong>бюджет</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
