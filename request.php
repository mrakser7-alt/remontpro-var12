<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/forms.php';

$user = current_user();
$formResult = process_repair_request($user, 'request.php?sent=1');

$repairTypes = repair_types_list();
$prefillType = trim((string)($_GET['type'] ?? ''));
$promo = null;
if (!empty($_GET['promo'])) {
    $stmt = db()->prepare('SELECT * FROM promos WHERE id = ? AND is_active = 1');
    $stmt->execute([(int)$_GET['promo']]);
    $promo = $stmt->fetch() ?: null;
}

$activePromos = cache_remember('request_promos', 120, function () {
    return db()->query("SELECT * FROM promos WHERE is_active = 1 ORDER BY valid_until ASC LIMIT 3")->fetchAll();
});

$pageTitle = 'Заявка на ремонт — бесплатный замер и смета';
$pageDescription = 'Оставьте заявку на ремонт квартиры или коттеджа. Бесплатный выезд инженера, смета за 24 часа, консультация дизайнера.';
$activeMenu = 'request';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>Заявка на ремонт</span>
        </nav>
        <span class="eyebrow">Начнём с бесплатного замера</span>
        <h1>Заявка на ремонт</h1>
        <p>Заполните форму — менеджер свяжется с вами в течение рабочего дня, уточнит детали и назначит удобное время замера.</p>
    </div>
</section>

<section class="section">
    <div class="container detail-grid">
        <div data-reveal>
            <?php if ($promo): ?>
                <div class="info-callout mb-3">
                    <svg viewBox="0 0 24 24"><path d="M12 2l2.4 6.9H22l-6.1 4.4 2.3 7.2-6.2-4.4-6.2 4.4 2.3-7.2L2 8.9h7.6L12 2z"/></svg>
                    <div>Вы подаёте заявку по акции <strong>«<?= e($promo['title']) ?>»</strong>. Скидка <strong><?= e($promo['discount']) ?></strong> будет учтена в смете.</div>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <div class="form-card__head">
                    <h2>Расскажите о вашем объекте</h2>
                    <p>Чем подробнее опишете задачу, тем точнее будет предварительная оценка.</p>
                </div>
                <?php if ($formResult['errors']): ?>
                    <div class="flash flash--error mb-3"><?= e(reset($formResult['errors'])) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= url('request.php') ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="form" value="repair_request">
                    <div class="field hp-field" aria-hidden="true">
                        <label for="hp-request">Не заполняйте</label>
                        <input type="text" id="hp-request" name="website_url_hp" tabindex="-1" autocomplete="off">
                    </div>

                    <h3 class="mb-2" style="font-size:15px">1. Контактные данные</h3>
                    <div class="form-grid">
                        <div class="field">
                            <label for="r-name">Ваше имя <span class="req">*</span></label>
                            <input type="text" id="r-name" name="name" value="<?= e($_POST['name'] ?? $user['name'] ?? '') ?>" placeholder="Как к вам обращаться" required>
                            <?php if (isset($formResult['errors']['name'])): ?><span class="field__error"><?= e($formResult['errors']['name']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="r-phone">Телефон <span class="req">*</span></label>
                            <input type="tel" id="r-phone" name="phone" value="<?= e($_POST['phone'] ?? $user['phone'] ?? '') ?>" placeholder="+7 (___) ___-__-__" required>
                            <?php if (isset($formResult['errors']['phone'])): ?><span class="field__error"><?= e($formResult['errors']['phone']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="r-email">E-mail</label>
                            <input type="email" id="r-email" name="email" value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>" placeholder="you@mail.ru">
                            <?php if (isset($formResult['errors']['email'])): ?><span class="field__error"><?= e($formResult['errors']['email']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="r-address">Адрес объекта</label>
                            <input type="text" id="r-address" name="address" value="<?= e($_POST['address'] ?? '') ?>" placeholder="Город, улица, дом">
                        </div>
                    </div>

                    <h3 class="mb-2 mt-4" style="font-size:15px">2. Параметры ремонта</h3>
                    <div class="form-grid">
                        <div class="field">
                            <label for="r-type">Тип ремонта <span class="req">*</span></label>
                            <select id="r-type" name="repair_type" required>
                                <option value="">Выберите тип</option>
                                <?php foreach ($repairTypes as $type): ?>
                                    <?php $selectedType = $_POST['repair_type'] ?? $prefillType; ?>
                                    <option value="<?= e($type) ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($formResult['errors']['repair_type'])): ?><span class="field__error"><?= e($formResult['errors']['repair_type']) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="r-area">Площадь, м²</label>
                            <input type="number" id="r-area" name="area" min="0" max="2000" value="<?= e((string)($_POST['area'] ?? '')) ?>" placeholder="Например, 65">
                        </div>
                        <div class="field form-row--full">
                            <label for="r-budget">Ориентировочный бюджет, ₽</label>
                            <input type="number" id="r-budget" name="budget" min="0" step="10000" value="<?= e((string)($_POST['budget'] ?? '')) ?>" placeholder="Например, 1 500 000">
                        </div>
                    </div>

                    <h3 class="mb-2 mt-4" style="font-size:15px">3. Задача</h3>
                    <div class="field">
                        <label for="r-message">Опишите задачу</label>
                        <textarea id="r-message" name="message" rows="5" maxlength="2000" data-counter="#r-counter" placeholder="Что нужно сделать, какие сроки, есть ли дизайн-проект, пожелания по материалам"><?= e($_POST['message'] ?? '') ?></textarea>
                        <span class="field__hint" id="r-counter">0 / 2000</span>
                        <?php if (isset($formResult['errors']['message'])): ?><span class="field__error"><?= e($formResult['errors']['message']) ?></span><?php endif; ?>
                    </div>

                    <div class="field field--check mt-3">
                        <input type="checkbox" id="r-consent" name="consent" value="1" <?= !empty($_POST['consent']) ? 'checked' : '' ?> required>
                        <label for="r-consent">Согласен с <a href="<?= url('about.php') ?>#documents" target="_blank">политикой обработки персональных данных</a> и на обработку моих данных для связи по заявке. <span class="req">*</span></label>
                    </div>
                    <?php if (isset($formResult['errors']['consent'])): ?><span class="field__error"><?= e($formResult['errors']['consent']) ?></span><?php endif; ?>

                    <div class="form-actions">
                        <button class="btn btn--primary btn--lg" type="submit">Отправить заявку
                            <svg viewBox="0 0 24 24"><path d="M13.2 5.4l6.4 6.6-6.4 6.6-1.4-1.4 4-4.1H4v-2h11.8l-4-4.1z"/></svg>
                        </button>
                        <span class="form-note">Нажимая кнопку, вы подтверждаете согласие с условиями обработки данных.</span>
                    </div>
                </form>
            </div>
        </div>

        <aside class="cabinet-main" data-reveal>
            <div class="aside-card">
                <h3>Что будет после заявки</h3>
                <div class="timeline mt-3">
                    <div class="timeline-item2">
                        <span>В течение дня</span>
                        <strong>Звонок менеджера</strong>
                        <p>Уточним задачу и подберём удобное время замера.</p>
                    </div>
                    <div class="timeline-item2">
                        <span>1–2 дня</span>
                        <strong>Выезд инженера</strong>
                        <p>Бесплатный замер и оценка состояния объекта.</p>
                    </div>
                    <div class="timeline-item2">
                        <span>24 часа</span>
                        <strong>Готовая смета</strong>
                        <p>Подробный расчёт с фиксированными ценами и графиком.</p>
                    </div>
                </div>
            </div>

            <div class="aside-card">
                <h3>Почему это безопасно</h3>
                <ul class="check-list mt-2">
                    <li>Никаких навязчивых звонков и спама</li>
                    <li>Замер и консультация бесплатны</li>
                    <li>Смета не обязывает заключать договор</li>
                    <li>Данные защищены и не передаются третьим лицам</li>
                </ul>
                <hr style="border:0;border-top:1px solid var(--line);margin:20px 0">
                <p class="text-muted" style="font-size:13px">Удобнее позвонить?</p>
                <a class="footer__phone" style="color:var(--ink);font-size:20px;font-weight:800" href="tel:+74951204500">+7 (495) 120-45-00</a>
                <p class="form-note">Ежедневно 9:00–21:00</p>
            </div>

            <?php if ($activePromos): ?>
                <div class="aside-card">
                    <h3>Действующие акции</h3>
                    <ul class="aside-list">
                        <?php foreach ($activePromos as $p): ?>
                            <li><span><?= e($p['title']) ?></span><strong><?= e($p['discount']) ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                    <a class="link-arrow" href="<?= url('promos.php') ?>">Все предложения</a>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
