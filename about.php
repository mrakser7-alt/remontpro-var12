<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/art.php';

$pageTitle = 'О компании — 14 лет профессионального ремонта';
$pageDescription = 'История, миссия и преимущества компании РемонтПро. Собственные бригады, инженеры и дизайнеры, гарантия 3 года на ремонт квартир и коттеджей.';
$activeMenu = 'about';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>О компании</span>
        </nav>
        <span class="eyebrow">Наша история</span>
        <h1>Ремонт с 2012 года:<br>от бригады до строительной компании</h1>
        <p>Мы начинали с небольшой команды отделочников и одним правилом: делать так, как для себя. Сегодня РемонтПро — это 38 специалистов, собственный склад материалов и 420 сданных объектов в Москве и области.</p>
    </div>
</section>

<section class="section">
    <div class="container about-split">
        <div data-reveal>
            <span class="eyebrow">Миссия</span>
            <h2>Сделать качественный ремонт понятным и предсказуемым</h2>
            <p class="text-muted">Ремонт — один из самых стрессовых опытов в жизни человека. Мы построили компанию вокруг идеи, что этого можно избежать: понятная смета, честные сроки, ежедневная связь и технически грамотные решения. Клиент не должен разбираться в стяжках и электрике — это наша работа.</p>
            <p class="text-muted">Поэтому мы инвестируем в инженерную экспертизу, собственных прорабов и прозрачную отчётность. Каждый объект ведётся в единой системе: вы видите этапы, смету и фото работ в личном кабинете.</p>
            <div class="flex flex-wrap gap-2 mt-3">
                <a class="btn btn--dark" href="<?= url('request.php') ?>">Обсудить проект</a>
                <a class="btn btn--ghost" href="<?= url('portfolio.php') ?>">Наши работы</a>
            </div>
        </div>
        <div class="about-split__visual" data-reveal>
            <div class="about-photo"><?= room_art('clay', '🏗️') ?></div>
            <div class="about-exp"><strong>420+</strong><span>сданных объектов</span></div>
        </div>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="section-head section-head--center">
            <div class="section-head__text">
                <span class="eyebrow">Цифры</span>
                <h2>Компания в цифрах</h2>
                <p>Мы не любим громкие обещания — предпочитаем подтверждать их результатом.</p>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card" data-reveal><div class="stat-card__num">14 <span>лет</span></div><div class="stat-card__label">на рынке ремонта Москвы и области</div></div>
            <div class="stat-card" data-reveal><div class="stat-card__num">420 <span>+</span></div><div class="stat-card__label">завершённых квартир и коттеджей</div></div>
            <div class="stat-card" data-reveal><div class="stat-card__num">38</div><div class="stat-card__label">штатных специалистов: от прораба до дизайнера</div></div>
            <div class="stat-card" data-reveal><div class="stat-card__num">96 <span>%</span></div><div class="stat-card__label">клиентов рекомендуют нас друзьям и знакомым</div></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Преимущества</span>
                <h2>Почему клиенты выбирают РемонтПро</h2>
                <p>Шесть принципов, на которых держится каждый наш проект.</p>
            </div>
        </div>
        <div class="values-grid">
            <div class="value-card" data-reveal>
                <span class="service-card__icon"><svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:#c07d12"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3zm-1.2 14.4l5.7-5.7-1.4-1.4-4.3 4.3-2-2-1.4 1.4 3.4 3.4z"/></svg></span>
                <h3>Гарантия 3 года</h3>
                <p>На все работы и 5 лет на инженерные системы. Гарантия прописана в договоре, а не на словах.</p>
            </div>
            <div class="value-card" data-reveal>
                <span class="service-card__icon"><svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:#c07d12"><path d="M4 4h16v16H4V4zm2 2v2h12V6H6zm0 4v8h12v-8H6z"/></svg></span>
                <h3>Фиксированная смета</h3>
                <p>Стоимость работ закрепляется в договоре. Дополнительные работы — только с вашего письменного согласия.</p>
            </div>
            <div class="value-card" data-reveal>
                <span class="service-card__icon"><svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:#c07d12"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 5v5l4 2-.8 1.8L11 13V7h2z"/></svg></span>
                <h3>Сроки под контролем</h3>
                <p>График работ составляется до старта. За каждый день просрочки по нашей вине предусмотрена компенсация.</p>
            </div>
            <div class="value-card" data-reveal>
                <span class="service-card__icon"><svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:#c07d12"><path d="M12 2a5 5 0 015 5c0 2.2-1.4 4-3.4 4.7L15 14h2v2h-2v2h2v2H7v-2h2v-4H7v-2h2l1.4-2.3A5 5 0 0112 2zm0 2a3 3 0 100 6 3 3 0 000-6z"/></svg></span>
                <h3>Собственные бригады</h3>
                <p>Все специалисты работают в штате. Мы не передаём объекты сомнительным субподрядчикам.</p>
            </div>
            <div class="value-card" data-reveal>
                <span class="service-card__icon"><svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:#c07d12"><path d="M3 5h18v2H3V5zm0 6h12v2H3v-2zm0 6h18v2H3v-2z"/></svg></span>
                <h3>Отчёты каждый день</h3>
                <p>Фото- и видеоотчёты, журнал работ и смета доступны онлайн. Вы контролируете ремонт из любой точки.</p>
            </div>
            <div class="value-card" data-reveal>
                <span class="service-card__icon"><svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:#c07d12"><path d="M12 2l2.4 6.9H22l-6.1 4.4 2.3 7.2-6.2-4.4-6.2 4.4 2.3-7.2L2 8.9h7.6L12 2z"/></svg></span>
                <h3>Один договор</h3>
                <p>Проектирование, закупка материалов, работы и уборка — в одном договоре и с одной ответственностью.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section--dark">
    <div class="container">
        <div class="about-split">
            <div data-reveal>
                <span class="eyebrow">Как мы работаем</span>
                <h2>Пять этапов от заявки до новоселья</h2>
                <div class="timeline mt-4">
                    <div class="timeline-item2">
                        <span>Этап 01</span>
                        <strong>Заявка и консультация</strong>
                        <p>Уточняем задачу по телефону, отвечаем на вопросы и назначаем удобное время замера.</p>
                    </div>
                    <div class="timeline-item2">
                        <span>Этап 02</span>
                        <strong>Замер и смета</strong>
                        <p>Инженер выезжает на объект, фиксирует объёмы и в течение 24 часов готовит прозрачную смету.</p>
                    </div>
                    <div class="timeline-item2">
                        <span>Этап 03</span>
                        <strong>Договор и график</strong>
                        <p>Подписываем договор с фиксированной стоимостью и календарным планом работ.</p>
                    </div>
                    <div class="timeline-item2">
                        <span>Этап 04</span>
                        <strong>Ремонт и контроль</strong>
                        <p>Работаем по графику, ежедневно присылаем отчёты. Все изменения согласуются письменно.</p>
                    </div>
                    <div class="timeline-item2">
                        <span>Этап 05</span>
                        <strong>Сдача и гарантия</strong>
                        <p>Принимаем объект вместе с вами, устраняем замечания и выдаём гарантийный талон.</p>
                    </div>
                </div>
            </div>
            <div data-reveal>
                <div class="about-photo"><?= room_art('night', '🔑') ?></div>
                <div class="info-callout mt-3">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    <div>Ведём работы по всей Москве и Московской области. Для загородных объектов организуем проживание бригады на участке.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="documents">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Документы</span>
                <h2>Юридическая прозрачность</h2>
                <p>Работаем официально: договор, акты, чеки и гарантийные обязательства.</p>
            </div>
            <a class="btn btn--ghost" href="<?= url('contacts.php') ?>">Запросить документы</a>
        </div>
        <div class="doc-list">
            <div class="doc-item" data-reveal>
                <svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>
                <div><strong>Договор подряда с фиксированной сметой</strong><span>Приложение с перечнем работ, материалов и графиком</span></div>
            </div>
            <div class="doc-item" data-reveal>
                <svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>
                <div><strong>Политика обработки персональных данных</strong><span>Собираем только данные, необходимые для оказания услуг</span></div>
            </div>
            <div class="doc-item" data-reveal>
                <svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>
                <div><strong>Гарантийный талон на 3 года</strong><span>Выдаётся после подписания акта приёмки работ</span></div>
            </div>
            <div class="doc-item" data-reveal>
                <svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5zM8 11h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>
                <div><strong>Свидетельство о регистрации ООО «РемонтПро»</strong><span>ИНН 7701234567, ОГРН 1127746000000</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="cta-band">
            <div class="cta-band__text">
                <span class="eyebrow" style="color:#f5c473">Знакомство</span>
                <h2>Приезжайте в офис или пригласите инженера на объект</h2>
                <p>Покажем примеры работ, материалы и сметы. Обсудим ваш проект без обязательств.</p>
                <div class="hero__actions mt-3">
                    <a class="btn btn--primary" href="<?= url('request.php') ?>">Записаться на замер</a>
                    <a class="btn btn--outline-light" href="<?= url('contacts.php') ?>">Контакты офиса</a>
                </div>
            </div>
            <div class="about-photo" style="border:0"><?= room_art('sage', '🤝') ?></div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
