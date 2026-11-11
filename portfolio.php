<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/art.php';

$pageTitle = 'Портфолио — выполненные проекты ремонта';
$pageDescription = 'Галерея выполненных проектов: квартиры, коттеджи, ванные комнаты и кухни. Реальные сроки, бюджеты и решения.';
$activeMenu = 'portfolio';

$selected = trim((string)($_GET['category'] ?? ''));
$categories = portfolio_categories();
if ($selected !== '' && !in_array($selected, $categories, true)) {
    $selected = '';
}

$projects = cache_remember('portfolio_' . md5($selected), 120, function () use ($selected) {
    if ($selected !== '') {
        $stmt = db()->prepare('SELECT * FROM portfolio WHERE category = ? ORDER BY is_featured DESC, created_at DESC');
        $stmt->execute([$selected]);
        return $stmt->fetchAll();
    }
    return db()->query('SELECT * FROM portfolio ORDER BY is_featured DESC, created_at DESC')->fetchAll();
});

$counts = cache_remember('portfolio_counts', 120, function () {
    return db()->query('SELECT category, COUNT(*) AS cnt FROM portfolio GROUP BY category')->fetchAll();
});
$countMap = array_column($counts, 'cnt', 'category');

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>Портфолио</span>
        </nav>
        <span class="eyebrow">Наши работы</span>
        <h1>Портфолио проектов</h1>
        <p>Каждый проект — это конкретный объект, площадь, бюджет и срок. Смотрите подробности и вдохновляйтесь решениями.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-bar">
            <a class="filter-chip<?= $selected === '' ? ' is-active' : '' ?>" href="<?= url('portfolio.php') ?>">Все проекты <span><?= array_sum($countMap) ?></span></a>
            <?php foreach ($categories as $cat): ?>
                <a class="filter-chip<?= $selected === $cat ? ' is-active' : '' ?>" href="<?= url('portfolio.php?category=' . urlencode($cat)) ?>"><?= e($cat) ?> <span><?= (int)($countMap[$cat] ?? 0) ?></span></a>
            <?php endforeach; ?>
        </div>

        <?php if (!$projects): ?>
            <div class="empty-state">
                <div class="empty-state__icon">🖼️</div>
                <h3>В этой категории пока нет проектов</h3>
                <p>Посмотрите другие направления ремонта.</p>
            </div>
        <?php else: ?>
            <div class="portfolio-grid portfolio-grid--3">
                <?php foreach ($projects as $project): ?>
                    <article class="project-card" data-reveal>
                        <a class="project-cover" href="<?= url('project.php?id=' . (int)$project['id']) ?>">
                            <?= room_art($project['tone'], $project['icon']) ?>
                            <span class="project-cover__cat"><?= e($project['category']) ?></span>
                            <span class="project-cover__area"><?= (int)$project['area'] ?> м²</span>
                        </a>
                        <div class="project-body">
                            <h3><a href="<?= url('project.php?id=' . (int)$project['id']) ?>"><?= e($project['title']) ?></a></h3>
                            <p><?= e(truncate_text($project['description'], 100)) ?></p>
                            <div class="project-meta">
                                <span><strong><?= e($project['duration']) ?></strong>срок</span>
                                <span><strong><?= format_price((int)$project['budget']) ?></strong>бюджет</span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="promo-banner">
            <div>
                <span class="eyebrow" style="color:#7a4a05">Хотите так же?</span>
                <h2>Обсудим ваш будущий проект</h2>
                <p>Расскажите о задаче — предложим решения, сроки и бюджет. Первая консультация бесплатная.</p>
            </div>
            <a class="btn btn--dark btn--lg" href="<?= url('request.php') ?>">Оставить заявку</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
