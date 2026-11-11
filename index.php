<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/art.php';
require_once __DIR__ . '/includes/forms.php';

$user = current_user();
$formResult = process_repair_request($user, 'index.php?sent=1');

$services = cache_remember('home_services', 120, function () {
    return db()->query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, id LIMIT 6')->fetchAll();
});
$promos = cache_remember('home_promos', 120, function () {
    return db()->query("SELECT * FROM promos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();
});
$projects = cache_remember('home_projects', 120, function () {
    return db()->query('SELECT * FROM portfolio ORDER BY is_featured DESC, created_at DESC LIMIT 4')->fetchAll();
});
$reviews = cache_remember('home_reviews', 120, function () {
    return db()->query("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3")->fetchAll();
});
$ratingStats = cache_remember('home_rating_stats', 120, function () {
    $row = db()->query("SELECT COUNT(*) AS cnt, COALESCE(AVG(rating), 0) AS avg FROM reviews WHERE status = 'approved'")->fetch();
    return ['count' => (int)($row['cnt'] ?? 0), 'avg' => round((float)($row['avg'] ?? 0), 1)];
});
$completedCount = (int)db()->query("SELECT COUNT(*) FROM requests WHERE status = 'completed'")->fetchColumn();
$repairTypes = repair_types_list();

$pageTitle = 'Профессиональный ремонт квартир и коттеджей в Москве';
$activeMenu = 'home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero__frame" data-slider>
            <div class="hero__bg"></div>
            <div class="hero__slides">
                <article class="hero__slide is-active">
                    <div class="hero__content">
                        <span class="hero__eyebrow">Акция месяца <b>−15% на капитальный ремонт</b></span>
                        <h1>Ремонт, которому<br>можно <em>доверять</em></h1>
                        <p class="hero__lead">Делаем квартиры и коттеджи под ключ с 2012 года. Фиксируем смету в договоре, работаем по графику и даём гарантию 3 года на все работы.</p>
                        <div class="hero__actions">
                            <a class="btn btn--primary btn--lg" href="<?= url('request.php') ?>">Рассчитать стоимость
                                <svg viewBox="0 0 24 24"><path d="M13.2 5.4l6.4 6.6-6.4 6.6-1.4-1.4 4-4.1H4v-2h11.8l-4-4.1z"/></svg>
                            </a>
                            <a class="btn btn--outline-light btn--lg" href="<?= url('portfolio.php') ?>">Смотреть работы</a>
                        </div>
                        <div class="hero__perks">
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3zm-1.2 14.4l5.7-5.7-1.4-1.4-4.3 4.3-2-2-1.4 1.4 3.4 3.4z"/></svg> Гарантия 3 года</span>
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M12 1.8a10.2 10.2 0 100 20.4 10.2 10.2 0 000-20.4zm.9 15.3h-1.8v-6h1.8v6zm0-7.5h-1.8V7.8h1.8v1.8z"/></svg> Смета без доплат</span>
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm0 3v13h10V5H7zm3 15h4v1h-4v-1z"/></svg> Отчёты онлайн</span>
                        </div>
                    </div>
                    <div class="hero__visual">
                        <div class="hero__art"><?= room_art('warm', '🛋️', true) ?></div>
                        <div class="hero__float">
                            <svg viewBox="0 0 24 24" fill="#3f8f7c"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3z"/></svg>
                            <div><strong>420+</strong><span>объектов сдано</span></div>
                        </div>
                    </div>
                </article>

                <article class="hero__slide">
                    <div class="hero__content">
                        <span class="hero__eyebrow">Загородные дома <b>от 100 до 700 м²</b></span>
                        <h1>Коттедж под ключ<br>от идеи до <em>новоселья</em></h1>
                        <p class="hero__lead">Инженерия, отделка, лестницы, фасад и благоустройство участка. Одна команда и один договор на весь объём работ.</p>
                        <div class="hero__actions">
                            <a class="btn btn--primary btn--lg" href="<?= url('request.php?type=' . urlencode('Ремонт коттеджа под ключ')) ?>">Обсудить проект</a>
                            <a class="btn btn--outline-light btn--lg" href="<?= url('services.php') ?>">Все услуги</a>
                        </div>
                        <div class="hero__perks">
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M12 3l9 8h-3v9h-5v-6h-2v6H6v-9H3l9-8z"/></svg> Работаем по всей области</span>
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm0 3v13h10V5H7z"/></svg> Личный прораб</span>
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3z"/></svg> Гарантия 3 года</span>
                        </div>
                    </div>
                    <div class="hero__visual">
                        <div class="hero__art"><?= room_art('sage', '🏡', true) ?></div>
                        <div class="hero__float">
                            <svg viewBox="0 0 24 24" fill="#3f8f7c"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3z"/></svg>
                            <div><strong>24</strong><span>коттеджа за год</span></div>
                        </div>
                    </div>
                </article>

                <article class="hero__slide">
                    <div class="hero__content">
                        <span class="hero__eyebrow">Подарок при заказе <b>от 60 м²</b></span>
                        <h1>Дизайн-проект<br>в <em>подарок</em></h1>
                        <p class="hero__lead">Планировка, 3D-визуализация и ведомость материалов — бесплатно при заказе ремонта под ключ. Вы увидите свой дом ещё до начала работ.</p>
                        <div class="hero__actions">
                            <a class="btn btn--primary btn--lg" href="<?= url('promos.php') ?>">Условия акции</a>
                            <a class="btn btn--outline-light btn--lg" href="<?= url('contacts.php') ?>">Задать вопрос</a>
                        </div>
                        <div class="hero__perks">
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3z"/></svg> Без скрытых платежей</span>
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2z"/></svg> 3D до начала работ</span>
                            <span class="hero__perk"><svg viewBox="0 0 24 24"><path d="M12 1.8a10.2 10.2 0 100 20.4 10.2 10.2 0 000-20.4zM11 7h2v7h-2V7zm0 9h2v2h-2v-2z"/></svg> Подбор материалов</span>
                        </div>
                    </div>
                    <div class="hero__visual">
                        <div class="hero__art"><?= room_art('cool', '✨', true) ?></div>
                        <div class="hero__float">
                            <svg viewBox="0 0 24 24" fill="#3f8f7c"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3z"/></svg>
                            <div><strong>0 ₽</strong><span>дизайн-проект</span></div>
                        </div>
                    </div>
                </article>
            </div>

            <div class="hero__nav">
                <div class="hero__dots" role="tablist" aria-label="Слайды">
                    <button class="hero__dot is-active" type="button" aria-selected="true" aria-label="Слайд 1"></button>
                    <button class="hero__dot" type="button" aria-selected="false" aria-label="Слайд 2"></button>
                    <button class="hero__dot" type="button" aria-selected="false" aria-label="Слайд 3"></button>
                </div>
                <div class="hero__arrows">
                    <button class="hero__arrow" type="button" data-slide-prev aria-label="Предыдущий слайд">
                        <svg viewBox="0 0 24 24"><path d="M14.4 5l-7 7 7 7 1.4-1.4L10.2 12l5.6-5.6z"/></svg>
                    </button>
                    <button class="hero__arrow" type="button" data-slide-next aria-label="Следующий слайд">
                        <svg viewBox="0 0 24 24"><path d="M9.6 5l7 7-7 7-1.4-1.4 5.6-5.6-5.6-5.6z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="trust-strip">
            <div class="trust-strip__inner">
                <div class="trust-item">
                    <svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3zm-1.2 14.4l5.7-5.7-1.4-1.4-4.3 4.3-2-2-1.4 1.4 3.4 3.4z"/></svg>
                    <div><strong>3 года гарантии</strong><span>на все виды работ</span></div>
                </div>
                <div class="trust-item">
                    <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4V4zm2 2v2h12V6H6zm0 4v8h12v-8H6zm2 2h4v4H8v-4zm6 0h2v2h-2v-2zm0 4h2v2h-2v-2z"/></svg>
                    <div><strong>Фиксированная смета</strong><span>прописана в договоре</span></div>
                </div>
                <div class="trust-item">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    <div><strong>Фотоотчёты каждый день</strong><span>контроль из любой точки</span></div>
                </div>
                <div class="trust-item">
                    <svg viewBox="0 0 24 24"><path d="M12 2l2.4 6.9H22l-6.1 4.4 2.3 7.2-6.2-4.4-6.2 4.4 2.3-7.2L2 8.9h7.6L12 2z"/></svg>
                    <div><strong><?= $completedCount ?>+ завершённых</strong><span>объектов и отзывов</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="about">
    <div class="container about-split">
        <div class="about-split__visual" data-reveal>
            <div class="about-photo"><?= room_art('sand', '📐') ?></div>
            <div class="about-exp"><strong>14</strong><span>лет на рынке ремонта</span></div>
        </div>
        <div data-reveal>
            <span class="eyebrow">О компании</span>
            <h2>Строим не просто ремонт,<br>а пространство для жизни</h2>
            <p class="text-muted">РемонтПро — команда инженеров, прорабов и дизайнеров, которая с 2012 года превращает квартиры и загородные дома в удобные и долговечные пространства. Мы верим, что качественный ремонт — это прозрачный процесс, а не лотерея.</p>
            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-item__icon"><svg viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z"/></svg></span>
                    <div><strong>Собственные бригады</strong><p>Не передаём объекты случайным подрядчикам — за качество отвечаем лично.</p></div>
                </div>
                <div class="feature-item">
                    <span class="feature-item__icon"><svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3zm-1.2 14.4l5.7-5.7-1.4-1.4-4.3 4.3-2-2-1.4 1.4 3.4 3.4z"/></svg></span>
                    <div><strong>Договор и гарантия</strong><p>Смета, сроки и гарантийные обязательства закреплены юридически.</p></div>
                </div>
                <div class="feature-item">
                    <span class="feature-item__icon"><svg viewBox="0 0 24 24"><path d="M3 5h18v2H3V5zm0 6h12v2H3v-2zm0 6h18v2H3v-2z"/></svg></span>
                    <div><strong>Прозрачность на каждом этапе</strong><p>Личный кабинет, фотоотчёты и смета в открытом доступе 24/7.</p></div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="btn btn--dark" href="<?= url('about.php') ?>">Подробнее о нас</a>
                <a class="btn btn--ghost" href="<?= url('team.php') ?>">Наша команда</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--paper" id="services">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Услуги</span>
                <h2>Полный цикл ремонтных работ</h2>
                <p>От косметического обновления до дизайнерского ремонта коттеджа. Выберите направление — мы рассчитаем стоимость и сроки.</p>
            </div>
            <a class="btn btn--ghost" href="<?= url('services.php') ?>">Все услуги
                <svg viewBox="0 0 24 24"><path d="M13.2 5.4l6.4 6.6-6.4 6.6-1.4-1.4 4-4.1H4v-2h11.8l-4-4.1z"/></svg>
            </a>
        </div>
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
    </div>
</section>

<section class="section" id="promos">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Акции и спецпредложения</span>
                <h2>Сейчас выгодно ремонтировать</h2>
                <p>Следите за сезонными предложениями — скидки на работы и бесплатные услуги для новых клиентов.</p>
            </div>
            <a class="btn btn--ghost" href="<?= url('promos.php') ?>">Все акции</a>
        </div>
        <div class="promos-grid">
            <?php foreach ($promos as $promo): ?>
                <article class="promo-card" data-reveal>
                    <div class="promo-card__top">
                        <span class="promo-card__badge"><?= e($promo['badge']) ?></span>
                        <span class="promo-card__discount"><?= e($promo['discount']) ?></span>
                    </div>
                    <div>
                        <h3><?= e($promo['title']) ?></h3>
                        <p><?= e(truncate_text($promo['description'], 120)) ?></p>
                    </div>
                    <div class="promo-card__foot">
                        <span>До <strong><?= e(format_date($promo['valid_until'])) ?></strong></span>
                        <a class="link-arrow" href="<?= url('request.php') ?>">Воспользоваться</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Портфолио</span>
                <h2>Проекты, за которые не стыдно</h2>
                <p>Реальные объекты с реальными сроками и бюджетами. Посмотрите, как мы работаем.</p>
            </div>
            <a class="btn btn--ghost" href="<?= url('portfolio.php') ?>">Смотреть все проекты</a>
        </div>
        <div class="portfolio-grid">
            <?php foreach ($projects as $project): ?>
                <article class="project-card" data-reveal>
                    <a class="project-cover" href="<?= url('project.php?id=' . (int)$project['id']) ?>">
                        <?= room_art($project['tone'], $project['icon']) ?>
                        <span class="project-cover__cat"><?= e($project['category']) ?></span>
                        <span class="project-cover__area"><?= (int)$project['area'] ?> м²</span>
                    </a>
                    <div class="project-body">
                        <h3><a href="<?= url('project.php?id=' . (int)$project['id']) ?>"><?= e($project['title']) ?></a></h3>
                        <p><?= e(truncate_text($project['description'], 110)) ?></p>
                        <div class="project-meta">
                            <span><strong><?= e($project['duration']) ?></strong>срок</span>
                            <span><strong><?= format_price((int)$project['budget']) ?></strong>бюджет</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="reviews">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Отзывы</span>
                <h2>Что говорят наши клиенты</h2>
                <p>Средняя оценка <?= e((string)$ratingStats['avg']) ?> из 5 на основе <?= (int)$ratingStats['count'] ?> опубликованных отзывов.</p>
            </div>
            <a class="btn btn--ghost" href="<?= url('reviews.php') ?>">Все отзывы</a>
        </div>
        <div class="rating-summary mb-3">
            <div>
                <div class="rating-summary__score"><?= e((string)$ratingStats['avg']) ?></div>
                <?= render_stars((int)round($ratingStats['avg'])) ?>
            </div>
            <div class="rating-summary__info">
                <strong><?= (int)$ratingStats['count'] ?> отзывов</strong><br>
                Проверенные клиенты после сдачи объекта
            </div>
            <div class="rating-summary__bars">
                <?php for ($star = 5; $star >= 1; $star--):
                    $cnt = 0;
                    foreach ($reviews as $r) { if ((int)$r['rating'] === $star) $cnt++; }
                    $pct = count($reviews) ? (int)round($cnt / count($reviews) * 100) : 0;
                ?>
                    <div class="rating-bar">
                        <span><?= $star ?>★</span>
                        <span class="rating-bar__track"><span class="rating-bar__fill" style="width:<?= $pct ?>%"></span></span>
                        <span><?= $cnt ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
                <article class="review-card" data-reveal>
                    <span class="review-card__quote">“</span>
                    <div class="review-card__head">
                        <span class="avatar" style="background:#8a9a5b"><?= e(initials($review['author_name'])) ?></span>
                        <div>
                            <strong><?= e($review['author_name']) ?></strong>
                            <span><?= e($review['city']) ?> · <?= e(format_date($review['created_at'])) ?></span>
                        </div>
                    </div>
                    <?= render_stars((int)$review['rating']) ?>
                    <p class="review-card__text"><?= e(truncate_text($review['text'], 220)) ?></p>
                    <?php if ($review['service']): ?>
                        <span class="review-card__service"><?= e($review['service']) ?></span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="cta-band" data-reveal>
            <div class="cta-band__text">
                <span class="eyebrow" style="color:#f5c473">Заявка на ремонт</span>
                <h2>Рассчитаем ваш ремонт бесплатно за 24 часа</h2>
                <p>Оставьте заявку — инженер свяжется с вами, уточнит детали и подготовит смету. Без навязчивых звонков и обязательств.</p>
                <div class="cta-points">
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Бесплатный выезд и замер</div>
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Смета с фиксированными ценами</div>
                    <div><svg viewBox="0 0 24 24"><path d="M9.6 17.2l-4.8-4.8 1.4-1.4 3.4 3.4 7.4-7.4 1.4 1.4z"/></svg> Консультация дизайнера в подарок</div>
                </div>
            </div>
            <div class="form-card">
                <div class="form-card__head">
                    <h3>Оставить заявку</h3>
                    <p>Заполните форму — перезвоним в течение рабочего дня.</p>
                </div>
                <?php if ($formResult['errors']): ?>
                    <div class="flash flash--error mb-2"><?= e(reset($formResult['errors'])) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= url('index.php') ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="form" value="repair_request">
                    <div class="field hp-field" aria-hidden="true">
                        <label for="hp-home">Не заполнять</label>
                        <input type="text" id="hp-home" name="website_url_hp" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="name-home">Ваше имя <span class="req">*</span></label>
                            <input type="text" id="name-home" name="name" value="<?= e($user['name'] ?? ($_POST['name'] ?? '')) ?>" placeholder="Александр" required>
                            <?php if (isset($formResult['errors']['name'])): ?><span class="field__error"><?= e($formResult['errors']['name']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="phone-home">Телефон <span class="req">*</span></label>
                            <input type="tel" id="phone-home" name="phone" value="<?= e($user['phone'] ?? ($_POST['phone'] ?? '')) ?>" placeholder="+7 (___) ___-__-__" required>
                            <?php if (isset($formResult['errors']['phone'])): ?><span class="field__error"><?= e($formResult['errors']['phone']) ?></span><?php endif; ?>
                        </div>
                        <div class="field form-row--full">
                            <label for="type-home">Тип ремонта <span class="req">*</span></label>
                            <select id="type-home" name="repair_type" required>
                                <option value="">Выберите направление</option>
                                <?php foreach ($repairTypes as $type): ?>
                                    <option value="<?= e($type) ?>" <?= ($_POST['repair_type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($formResult['errors']['repair_type'])): ?><span class="field__error"><?= e($formResult['errors']['repair_type']) ?></span><?php endif; ?>
                        </div>
                        <div class="field form-row--full">
                            <label for="message-home">Кратко о задаче</label>
                            <textarea id="message-home" name="message" rows="3" maxlength="2000" placeholder="Площадь, адрес объекта, пожелания по срокам"><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>
                        <div class="field field--check form-row--full">
                            <input type="checkbox" id="consent-home" name="consent" value="1" <?= !empty($_POST['consent']) ? 'checked' : '' ?> required>
                            <label for="consent-home">Согласен с <a href="<?= url('about.php') ?>#documents">политикой обработки персональных данных</a> и получением ответа на заявку.</label>
                        </div>
                    </div>
                    <?php if (isset($formResult['errors']['consent'])): ?><span class="field__error"><?= e($formResult['errors']['consent']) ?></span><?php endif; ?>
                    <div class="form-actions">
                        <button class="btn btn--primary btn--block" type="submit">Отправить заявку
                            <svg viewBox="0 0 24 24"><path d="M13.2 5.4l6.4 6.6-6.4 6.6-1.4-1.4 4-4.1H4v-2h11.8l-4-4.1z"/></svg>
                        </button>
                    </div>
                    <p class="form-note mt-2">Нажимая кнопку, вы соглашаетесь с условиями обработки данных. Мы не передаём контакты третьим лицам.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section section--warm">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Контакты</span>
                <h2>Свяжитесь удобным способом</h2>
            </div>
            <a class="btn btn--dark" href="<?= url('contacts.php') ?>">Контакты и карта</a>
        </div>
        <div class="stats-grid">
            <div class="stat-card" data-reveal>
                <div class="stat-card__label text-muted">Телефон</div>
                <div class="stat-card__num" style="font-size:22px"><a href="tel:+74951204500">+7 (495) 120-45-00</a></div>
                <div class="stat-card__label">Ежедневно 9:00–21:00</div>
            </div>
            <div class="stat-card" data-reveal>
                <div class="stat-card__label text-muted">E-mail</div>
                <div class="stat-card__num" style="font-size:22px"><a href="mailto:info@remontpro.ru">info@remontpro.ru</a></div>
                <div class="stat-card__label">Ответим в течение дня</div>
            </div>
            <div class="stat-card" data-reveal>
                <div class="stat-card__label text-muted">Офис</div>
                <div class="stat-card__num" style="font-size:22px">Москва, Б. Новодмитровская, 23</div>
                <div class="stat-card__label">Офис 214, 2 этаж</div>
            </div>
            <div class="stat-card" data-reveal>
                <div class="stat-card__label text-muted">Онлайн</div>
                <div class="stat-card__num" style="font-size:22px">Telegram / WhatsApp</div>
                <div class="stat-card__label">Напишите нам в мессенджер</div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
