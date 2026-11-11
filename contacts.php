<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/forms.php';

$formResult = process_contact('contacts.php?sent=1');

$pageTitle = 'Контакты — офис и карта проезда';
$pageDescription = 'Контакты компании РемонтПро: телефон +7 (495) 120-45-00, email info@remontpro.ru, офис в Москве на Большой Новодмитровской, 23. Форма обратной связи.';
$activeMenu = 'contacts';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>Контакты</span>
        </nav>
        <span class="eyebrow">Мы рядом</span>
        <h1>Контакты и карта проезда</h1>
        <p>Приезжайте в офис на чашку кофе — покажем материалы, примеры работ и обсудим ваш проект. Или напишите нам удобным способом.</p>
    </div>
</section>

<section class="section">
    <div class="container contacts-grid">
        <div data-reveal>
            <span class="eyebrow">Связаться с нами</span>
            <h2>РемонтПро в Москве</h2>
            <p class="text-muted">Отвечаем на звонки и сообщения ежедневно с 9:00 до 21:00. Выезд инженера на замер — бесплатно.</p>

            <div class="contact-list">
                <div class="contact-item">
                    <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.2 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.2 1.1l-2.3 1.9z"/></svg></span>
                    <div><strong>Телефон</strong><a href="tel:+74951204500">+7 (495) 120-45-00</a><p class="small">Ежедневно 9:00–21:00</p></div>
                </div>
                <div class="contact-item">
                    <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M3 6.5A2.5 2.5 0 015.5 4h13A2.5 2.5 0 0121 6.5v11a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 17.5v-11zm2.2-.5L12 11l6.8-5H5.2z"/></svg></span>
                    <div><strong>E-mail</strong><a href="mailto:info@remontpro.ru">info@remontpro.ru</a><p class="small">Ответим в течение рабочего дня</p></div>
                </div>
                <div class="contact-item">
                    <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 017 7c0 5.2-7 13-7 13S5 14.2 5 9a7 7 0 017-7zm0 4.5A2.5 2.5 0 109.5 9 2.5 2.5 0 0012 6.5z"/></svg></span>
                    <div><strong>Офис и шоурум</strong><p>Москва, ул. Большая Новодмитровская, 23, стр. 2</p><p class="small">Офис 214, 2 этаж. Вход со двора, парковка для клиентов</p></div>
                </div>
                <div class="contact-item">
                    <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 5v5l4 2-.8 1.8L11 13V7h2z"/></svg></span>
                    <div><strong>Режим работы</strong><p>Пн–Вс: 9:00–21:00</p><p class="small">Замеры на объектах — по предварительной записи</p></div>
                </div>
            </div>

            <div class="social-row">
                <a href="#" aria-label="Telegram"><svg viewBox="0 0 24 24"><path d="M21.9 4.3L18.6 20c-.2 1.1-.9 1.4-1.8.9l-5-3.7-2.4 2.3c-.3.3-.5.5-1 .5l.4-5 9.1-8.2c.4-.4-.1-.6-.6-.2L6.1 13.3 1.2 11.8c-1.1-.3-1.1-1.1.2-1.6l19-7.3c.9-.3 1.7.2 1.5 1.4z"/></svg></a>
                <a href="#" aria-label="WhatsApp"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2zm0 2a8 8 0 016.8 12.2l-.3.5.7 2.5-2.6-.7-.5.3A8 8 0 1112 4zm-3.1 4.3c-.2 0-.5.1-.7.4-.2.3-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.7 4.2 3.7 2.1.8 2.5.6 3 .6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.2-.2-.4-.3l-1.5-.7c-.2-.1-.4-.1-.5.1l-.7.9c-.1.2-.3.2-.5.1-.2-.1-1-.4-1.9-1.2-.7-.6-1.2-1.4-1.3-1.6-.1-.2 0-.3.1-.4l.3-.4c.1-.1.1-.2.2-.4.1-.1 0-.3 0-.4l-.7-1.7c-.2-.4-.3-.4-.5-.4h-.3z"/></svg></a>
                <a href="#" aria-label="VK"><svg viewBox="0 0 24 24"><path d="M13 17.5c-5.4 0-8.7-3.8-8.9-10h2.8c.1 4.6 2.2 6.5 3.7 6.9V7.5h2.6v3.9c1.5-.2 3.1-1.9 3.6-3.9h2.6c-.4 2.5-2 4.2-3.2 4.9 1.2.6 3 2.1 3.7 5.1h-2.9c-.5-1.8-1.9-3.2-3.8-3.4v3.4z"/></svg></a>
            </div>

            <div class="info-callout mt-4">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                <div>Работаем с юридическими лицами: возможна оплата по счёту, закрывающие документы и постоплата по этапам.</div>
            </div>
        </div>

        <div data-reveal>
            <div class="map-card">
                <svg viewBox="0 0 700 520" role="img" aria-label="Схема расположения офиса">
                    <rect width="700" height="520" fill="#eae4d8"/>
                    <path d="M0 360 C120 330 180 390 280 350 S440 260 540 300 660 350 700 320 L700 520 L0 520 Z" fill="#cfe0d8"/>
                    <path d="M-20 120 C140 160 240 90 360 120 S560 210 720 150" fill="none" stroke="#a9c9d8" stroke-width="34" opacity=".8"/>
                    <g stroke="#ffffff" stroke-width="16" stroke-linecap="round">
                        <path d="M-20 300 L720 260"/>
                        <path d="M120 -20 L160 540"/>
                        <path d="M430 -20 L400 540"/>
                        <path d="M0 440 L720 400"/>
                    </g>
                    <g stroke="#f7f3ea" stroke-width="7">
                        <path d="M-20 210 L720 180"/>
                        <path d="M250 -20 L270 540"/>
                        <path d="M560 -20 L540 540"/>
                    </g>
                    <g fill="#dcd3c3">
                        <rect x="40" y="40" width="52" height="46" rx="4"/>
                        <rect x="180" y="40" width="46" height="46" rx="4"/>
                        <rect x="300" y="36" width="66" height="52" rx="4"/>
                        <rect x="470" y="40" width="52" height="40" rx="4"/>
                        <rect x="600" y="60" width="58" height="46" rx="4"/>
                        <rect x="60" y="230" width="42" height="40" rx="4"/>
                        <rect x="300" y="230" width="50" height="42" rx="4"/>
                        <rect x="470" y="210" width="56" height="38" rx="4"/>
                        <rect x="620" y="220" width="42" height="38" rx="4"/>
                        <rect x="200" y="470" width="54" height="32" rx="4"/>
                        <rect x="450" y="460" width="60" height="38" rx="4"/>
                        <rect x="600" y="450" width="54" height="36" rx="4"/>
                    </g>
                    <g fill="#bcd4c6">
                        <circle cx="240" cy="160" r="38"/>
                        <circle cx="330" cy="330" r="26"/>
                        <circle cx="80" cy="380" r="30"/>
                    </g>
                    <g fill="#93b7a2" opacity=".7">
                        <circle cx="230" cy="150" r="16"/>
                        <circle cx="253" cy="174" r="14"/>
                        <circle cx="70" cy="372" r="12"/>
                    </g>
                    <text x="42" y="30" font-family="Manrope, sans-serif" font-size="12" fill="#8f8676">Б. Новодмитровская ул.</text>
                    <text x="470" y="500" font-family="Manrope, sans-serif" font-size="12" fill="#8f8676">м. Дмитровская · 7 мин</text>
                </svg>
                <div class="map-pin">РемонтПро
                    <small>Б. Новодмитровская, 23, стр. 2</small>
                </div>
            </div>
            <div class="panel mt-3" style="border-radius:var(--radius)">
                <div class="panel__head">
                    <div>
                        <h3>Как добраться</h3>
                        <p>На метро или автомобиле</p>
                    </div>
                </div>
                <div class="panel__body">
                    <ul class="check-list">
                        <li><strong>На метро:</strong> ст. Дмитровская, выход №3, далее 7 минут пешком в сторону Б. Новодмитровской улицы.</li>
                        <li><strong>На автомобиле:</strong> съезд с ТТК на ул. Образцова, поворот на Б. Новодмитровскую. Парковка во дворе бизнес-квартала.</li>
                        <li><strong>На автобусе:</strong> маршруты 12, 82, остановка «Новодмитровская, 23».</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section--paper" id="feedback">
    <div class="container">
        <div class="detail-grid">
            <div data-reveal>
                <span class="eyebrow">Обратная связь</span>
                <h2>Напишите нам</h2>
                <p class="text-muted">Есть вопрос по ремонту, хотите уточнить смету или предложить сотрудничество? Заполните форму — ответим на e-mail или перезвоним.</p>
                <div class="contact-list mt-3">
                    <div class="contact-item">
                        <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M12 2l8 3v6c0 5.2-3.4 9.6-8 11-4.6-1.4-8-5.8-8-11V5l8-3z"/></svg></span>
                        <div><strong>Гарантия ответа</strong><p>Отвечаем в течение одного рабочего дня</p></div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-item__icon"><svg viewBox="0 0 24 24"><path d="M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm0 3v13h10V5H7z"/></svg></span>
                        <div><strong>Для срочных вопросов</strong><a href="tel:+74951204500">+7 (495) 120-45-00</a></div>
                    </div>
                </div>
                <div class="info-callout mt-3">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    <div>Персональные данные используются только для ответа на обращение и не передаются третьим лицам.</div>
                </div>
            </div>

            <div class="form-card" data-reveal>
                <div class="form-card__head">
                    <h3>Форма обратной связи</h3>
                    <p>Поля со звёздочкой обязательны.</p>
                </div>
                <?php if ($formResult['errors']): ?>
                    <div class="flash flash--error mb-2"><?= e(reset($formResult['errors'])) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= url('contacts.php') ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="form" value="contact">
                    <div class="field hp-field" aria-hidden="true">
                        <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="c-name">Имя <span class="req">*</span></label>
                            <input type="text" id="c-name" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
                            <?php if (isset($formResult['errors']['name'])): ?><span class="field__error"><?= e($formResult['errors']['name']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="c-phone">Телефон</label>
                            <input type="tel" id="c-phone" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+7 (___) ___-__-__">
                        </div>
                        <div class="field">
                            <label for="c-email">E-mail</label>
                            <input type="email" id="c-email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@mail.ru">
                            <?php if (isset($formResult['errors']['email'])): ?><span class="field__error"><?= e($formResult['errors']['email']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="c-subject">Тема</label>
                            <input type="text" id="c-subject" name="subject" value="<?= e($_POST['subject'] ?? '') ?>" placeholder="Вопрос по ремонту">
                        </div>
                        <div class="field form-row--full">
                            <label for="c-message">Сообщение <span class="req">*</span></label>
                            <textarea id="c-message" name="message" rows="5" required maxlength="2000" data-counter="#c-counter" placeholder="Расскажите, чем мы можем помочь"><?= e($_POST['message'] ?? '') ?></textarea>
                            <span class="field__hint" id="c-counter">0 / 2000</span>
                            <?php if (isset($formResult['errors']['message'])): ?><span class="field__error"><?= e($formResult['errors']['message']) ?></span><?php endif; ?>
                        </div>
                        <div class="field field--check form-row--full">
                            <input type="checkbox" id="c-consent" name="consent" value="1" <?= !empty($_POST['consent']) ? 'checked' : '' ?> required>
                            <label for="c-consent">Согласен с политикой обработки персональных данных.</label>
                        </div>
                    </div>
                    <?php if (isset($formResult['errors']['consent'])): ?><span class="field__error"><?= e($formResult['errors']['consent']) ?></span><?php endif; ?>
                    <div class="form-actions">
                        <button class="btn btn--primary btn--block" type="submit">Отправить сообщение</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
