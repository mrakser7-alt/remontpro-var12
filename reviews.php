<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/forms.php';

$user = current_user();
$formResult = process_review($user, 'reviews.php#leave-review');

$ratingFilter = (int)($_GET['rating'] ?? 0);
$ratingFilter = ($ratingFilter >= 1 && $ratingFilter <= 5) ? $ratingFilter : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;

$stats = cache_remember('reviews_stats', 120, function () {
    $row = db()->query("SELECT COUNT(*) cnt, COALESCE(AVG(rating),0) avg FROM reviews WHERE status = 'approved'")->fetch();
    $bars = [];
    for ($i = 5; $i >= 1; $i--) {
        $bars[$i] = (int)db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved' AND rating = $i")->fetchColumn();
    }
    return ['count' => (int)$row['cnt'], 'avg' => round((float)$row['avg'], 1), 'bars' => $bars];
});

$where = "status = 'approved'";
$params = [];
if ($ratingFilter) {
    $where .= ' AND rating = ?';
    $params[] = $ratingFilter;
}
$countStmt = db()->prepare("SELECT COUNT(*) FROM reviews WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare("SELECT * FROM reviews WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$reviews = $stmt->fetchAll();

$canReview = false;
$reviewReason = '';
$completedRequests = 0;
$pendingReview = null;
if ($user) {
    if (in_array($user['role'], ['admin', 'manager'], true)) {
        $reviewReason = 'Вы вошли как сотрудник. Отзывы оставляют клиенты после завершения ремонта.';
    } else {
        $stmt = db()->prepare("SELECT COUNT(*) FROM requests WHERE user_id = ? AND status = 'completed'");
        $stmt->execute([$user['id']]);
        $completedRequests = (int)$stmt->fetchColumn();
        $canReview = $completedRequests > 0;
        if (!$canReview) {
            $reviewReason = 'Отзыв можно оставить после того, как мы завершим ваш ремонт. У вас пока нет завершённых заявок.';
        }
        $stmt = db()->prepare("SELECT * FROM reviews WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user['id']]);
        $pendingReview = $stmt->fetch() ?: null;
    }
}

$services = cache_remember('review_services', 120, function () {
    return db()->query('SELECT title FROM services WHERE is_active = 1 ORDER BY sort_order')->fetchAll(PDO::FETCH_COLUMN);
});

$pageTitle = 'Отзывы клиентов о ремонте';
$pageDescription = 'Реальные отзывы клиентов о ремонте квартир и коттеджей. Средняя оценка ' . $stats['avg'] . ' из 5. Оставить свой отзыв.';
$activeMenu = 'reviews';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>Отзывы</span>
        </nav>
        <span class="eyebrow">Мнения клиентов</span>
        <h1>Отзывы о нашей работе</h1>
        <p>Публикуем только отзывы реальных клиентов после сдачи объекта. Каждый отзыв проходит проверку модератором.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="rating-summary mb-4" data-reveal>
            <div>
                <div class="rating-summary__score"><?= e((string)$stats['avg']) ?></div>
                <?= render_stars((int)round($stats['avg'])) ?>
            </div>
            <div class="rating-summary__info">
                <strong><?= (int)$stats['count'] ?> проверенных отзывов</strong><br>
                Средняя оценка по всем завершённым проектам
            </div>
            <div class="rating-summary__bars">
                <?php foreach ($stats['bars'] as $star => $cnt): ?>
                    <div class="rating-bar">
                        <a href="<?= url('reviews.php?rating=' . $star) ?>" style="color:inherit"><?= $star ?>★</a>
                        <span class="rating-bar__track"><span class="rating-bar__fill" style="width:<?= $stats['count'] ? (int)round($cnt / $stats['count'] * 100) : 0 ?>%"></span></span>
                        <span><?= (int)$cnt ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <a class="btn btn--dark" href="#leave-review">Оставить отзыв</a>
        </div>

        <div class="filter-bar">
            <a class="filter-chip<?= !$ratingFilter ? ' is-active' : '' ?>" href="<?= url('reviews.php') ?>">Все отзывы <span><?= (int)$stats['count'] ?></span></a>
            <?php for ($star = 5; $star >= 1; $star--): ?>
                <a class="filter-chip<?= $ratingFilter === $star ? ' is-active' : '' ?>" href="<?= url('reviews.php?rating=' . $star) ?>"><?= $star ?> ★ <span><?= (int)$stats['bars'][$star] ?></span></a>
            <?php endfor; ?>
        </div>

        <?php if (!$reviews): ?>
            <div class="empty-state">
                <div class="empty-state__icon">💬</div>
                <h3>Пока нет отзывов с такой оценкой</h3>
                <p>Попробуйте сбросить фильтр.</p>
            </div>
        <?php else: ?>
            <div class="reviews-grid">
                <?php foreach ($reviews as $review): ?>
                    <article class="review-card" data-reveal>
                        <span class="review-card__quote">“</span>
                        <div class="review-card__head">
                            <span class="avatar" style="background:<?= e(['#8a9a5b', '#c76b8e', '#6f7fd6', '#4d9d8a', '#b8823d'][$review['id'] % 5]) ?>"><?= e(initials($review['author_name'])) ?></span>
                            <div>
                                <strong><?= e($review['author_name']) ?></strong>
                                <span><?= e($review['city']) ?> · <?= e(format_date($review['created_at'])) ?></span>
                            </div>
                        </div>
                        <?= render_stars((int)$review['rating']) ?>
                        <p class="review-card__text"><?= nl2br(e($review['text'])) ?></p>
                        <?php if ($review['service']): ?>
                            <span class="review-card__service"><?= e($review['service']) ?></span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('reviews.php?page=' . ($page - 1) . ($ratingFilter ? '&rating=' . $ratingFilter : '')) ?>">←</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="is-current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= url('reviews.php?page=' . $i . ($ratingFilter ? '&rating=' . $ratingFilter : '')) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $pages): ?>
                        <a href="<?= url('reviews.php?page=' . ($page + 1) . ($ratingFilter ? '&rating=' . $ratingFilter : '')) ?>">→</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<section class="section section--paper" id="leave-review">
    <div class="container">
        <div class="detail-grid">
            <div data-reveal>
                <span class="eyebrow">Обратная связь</span>
                <h2>Поделитесь впечатлениями о ремонте</h2>
                <p class="text-muted">Нам важно знать, что получилось хорошо, а над чем стоит поработать. Отзыв появится на сайте после проверки модератором — обычно это занимает не больше одного рабочего дня.</p>
                <ul class="check-list mt-3">
                    <li>Отзывы оставляют только авторизованные клиенты с завершённым ремонтом</li>
                    <li>Мы не редактируем содержание отзывов</li>
                    <li>Модерация отсеивает спам и нерелевантные сообщения</li>
                </ul>
                <?php if ($pendingReview): ?>
                    <div class="info-callout mt-3">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <div>Ваш отзыв от <?= e(format_date($pendingReview['created_at'])) ?> находится на модерации.</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-card" data-reveal>
                <?php if (!$user): ?>
                    <div class="form-card__head">
                        <h3>Хотите оставить отзыв?</h3>
                        <p>Войдите в личный кабинет — форма станет доступна автоматически.</p>
                    </div>
                    <a class="btn btn--primary btn--block" href="<?= url('login.php?redirect=reviews.php%23leave-review') ?>">Войти в аккаунт</a>
                    <p class="form-note mt-3 text-center">Ещё нет аккаунта? <a href="<?= url('register.php') ?>"><strong>Зарегистрируйтесь</strong></a> — это займёт меньше минуты.</p>
                <?php elseif (!$canReview): ?>
                    <div class="form-card__head">
                        <h3>Форма пока недоступна</h3>
                        <p><?= e($reviewReason) ?></p>
                    </div>
                    <a class="btn btn--ghost btn--block" href="<?= url('request.php') ?>">Оставить заявку на ремонт</a>
                    <?php if ($user['role'] === 'user'): ?>
                        <p class="form-note mt-3 text-center">Завершённых заявок в вашем кабинете: <strong><?= $completedRequests ?></strong></p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="form-card__head">
                        <h3>Оставить отзыв</h3>
                        <p>Спасибо, что доверили нам свой ремонт!</p>
                    </div>
                    <?php if ($formResult['errors']): ?>
                        <div class="flash flash--error mb-2">
                            <?= e(reset($formResult['errors'])) ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" action="<?= url('reviews.php') ?>#leave-review" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="form" value="review">
                        <div class="field hp-field" aria-hidden="true">
                            <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="field mb-2">
                            <label>Ваша оценка <span class="req">*</span></label>
                            <div class="rating-input">
                                <?php for ($star = 5; $star >= 1; $star--): ?>
                                    <input type="radio" id="rating-<?= $star ?>" name="rating" value="<?= $star ?>" <?= (int)($_POST['rating'] ?? 5) === $star ? 'checked' : '' ?>>
                                    <label for="rating-<?= $star ?>" title="<?= $star ?> из 5">
                                        <svg viewBox="0 0 24 24"><path d="M12 2.6l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.5 6.1 20.6l1.2-6.5L2.5 9.5l6.6-.9L12 2.6z"/></svg>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="field">
                                <label for="author_name">Имя <span class="req">*</span></label>
                                <input type="text" id="author_name" name="author_name" value="<?= e($_POST['author_name'] ?? $user['name']) ?>" required>
                            </div>
                            <div class="field">
                                <label for="city">Город</label>
                                <input type="text" id="city" name="city" value="<?= e($_POST['city'] ?? 'Москва') ?>">
                            </div>
                            <div class="field form-row--full">
                                <label for="review-service">Услуга</label>
                                <select id="review-service" name="service">
                                    <option value="">Не указывать</option>
                                    <?php foreach ($services as $serviceTitle): ?>
                                        <option value="<?= e($serviceTitle) ?>" <?= ($_POST['service'] ?? '') === $serviceTitle ? 'selected' : '' ?>><?= e($serviceTitle) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field form-row--full">
                                <label for="review-text">Отзыв <span class="req">*</span></label>
                                <textarea id="review-text" name="text" rows="5" maxlength="1500" data-counter="#review-counter" placeholder="Что понравилось, как прошли работы, что можно улучшить"><?= e($_POST['text'] ?? '') ?></textarea>
                                <span class="field__hint" id="review-counter">0 / 1500</span>
                                <?php if (isset($formResult['errors']['text'])): ?><span class="field__error"><?= e($formResult['errors']['text']) ?></span><?php endif; ?>
                            </div>
                            <div class="field field--check form-row--full">
                                <input type="checkbox" id="review-consent" name="consent" value="1" <?= !empty($_POST['consent']) ? 'checked' : '' ?> required>
                                <label for="review-consent">Согласен на публикацию отзыва и обработку персональных данных.</label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn--primary btn--block" type="submit">Отправить отзыв</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
