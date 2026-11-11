<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Акции и специальные предложения';
$pageDescription = 'Действующие скидки на ремонт квартир и коттеджей: капитальный ремонт −15%, дизайн-проект в подарок, бесплатный замер и другие предложения.';
$activeMenu = 'promos';

$promos = cache_remember('promos_all', 120, function () {
    return db()->query('SELECT * FROM promos ORDER BY is_active DESC, valid_until ASC, created_at DESC')->fetchAll();
});
$activePromos = array_values(array_filter($promos, fn($p) => (int)$p['is_active'] === 1 && (!$p['valid_until'] || strtotime($p['valid_until']) >= strtotime('today'))));
$expiredPromos = array_values(array_filter($promos, fn($p) => !in_array($p, $activePromos, true)));

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>Акции</span>
        </nav>
        <span class="eyebrow">Выгодные предложения</span>
        <h1>Акции и скидки</h1>
        <p>Мы регулярно запускаем сезонные предложения, чтобы ремонт был не только качественным, но и выгодным. Следите за обновлениями.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($activePromos): ?>
            <div class="promos-grid">
                <?php foreach ($activePromos as $promo): ?>
                    <article class="promo-card" data-reveal>
                        <div class="promo-card__top">
                            <span class="promo-card__badge"><?= e($promo['badge']) ?></span>
                            <span class="promo-card__discount"><?= e($promo['discount']) ?></span>
                        </div>
                        <div>
                            <h3><?= e($promo['title']) ?></h3>
                            <p><?= e($promo['description']) ?></p>
                        </div>
                        <div class="promo-card__foot">
                            <span>Действует до <strong><?= e(format_date($promo['valid_until'])) ?></strong></span>
                            <a class="btn btn--primary btn--sm" href="<?= url('request.php?promo=' . (int)$promo['id']) ?>">Получить</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state__icon">🎁</div>
                <h3>Сейчас активных акций нет</h3>
                <p>Оставьте заявку — менеджер расскажет о ближайших специальных предложениях.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Условия</span>
                <h2>Как получить скидку</h2>
                <p>Все акции действуют при заключении договора на ремонт в указанный период.</p>
            </div>
        </div>
        <div class="values-grid">
            <div class="value-card">
                <span class="service-card__icon">1️⃣</span>
                <h3>Оставьте заявку</h3>
                <p>Укажите акцию в комментарии к заявке или сообщите менеджеру при звонке.</p>
            </div>
            <div class="value-card">
                <span class="service-card__icon">2️⃣</span>
                <h3>Согласуйте смету</h3>
                <p>Мы рассчитаем стоимость с учётом скидки и зафиксируем условия в договоре.</p>
            </div>
            <div class="value-card">
                <span class="service-card__icon">3️⃣</span>
                <h3>Заключите договор</h3>
                <p>Скидка применяется при подписании договора в период действия акции.</p>
            </div>
        </div>
        <div class="info-callout mt-4">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            <div>Акции не суммируются между собой, если в условиях не указано иное. Скидка не распространяется на стоимость материалов. Подробные условия уточняйте у менеджера.</div>
        </div>
    </div>
</section>

<?php if ($expiredPromos): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Архив</span>
                <h2>Завершённые акции</h2>
            </div>
        </div>
        <div class="promos-grid">
            <?php foreach ($expiredPromos as $promo): ?>
                <article class="promo-card promo-expired">
                    <div class="promo-card__top">
                        <span class="promo-card__badge">Завершена</span>
                        <span class="promo-card__discount"><?= e($promo['discount']) ?></span>
                    </div>
                    <div>
                        <h3><?= e($promo['title']) ?></h3>
                        <p><?= e($promo['description']) ?></p>
                    </div>
                    <div class="promo-card__foot">
                        <span>Закончилась <?= e(format_date($promo['valid_until'])) ?></span>
                        <a class="link-arrow" href="<?= url('request.php') ?>">Узнать о новых</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section--tight">
    <div class="container">
        <div class="promo-banner">
            <div>
                <span class="eyebrow" style="color:#7a4a05">Персональное предложение</span>
                <h2>Подберём акцию под ваш объект</h2>
                <p>Расскажите о задаче — найдём оптимальное сочетание цены, сроков и объёма работ.</p>
            </div>
            <a class="btn btn--dark btn--lg" href="<?= url('request.php') ?>">Получить консультацию</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
