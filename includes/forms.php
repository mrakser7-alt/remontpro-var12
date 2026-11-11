<?php
/**
 * Обработчики форм: заявка на ремонт, отзыв, обратная связь.
 * Включает CSRF-проверку, honeypot и ограничение частоты отправки.
 */

function repair_types_list(): array
{
    $stmt = db()->query("SELECT title FROM services WHERE is_active = 1 ORDER BY sort_order, id");
    return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

/**
 * @return array{errors: array<string,string>, done: bool}
 */
function process_repair_request(?array $user, string $redirectTo = 'request.php'): array
{
    $result = ['errors' => [], 'done' => false];
    if (!is_post() || ($_POST['form'] ?? '') !== 'repair_request') {
        return $result;
    }

    $data = [
        'name' => trim((string)($_POST['name'] ?? '')),
        'phone' => trim((string)($_POST['phone'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'repair_type' => trim((string)($_POST['repair_type'] ?? '')),
        'area' => int_or_zero($_POST['area'] ?? 0),
        'budget' => int_or_zero($_POST['budget'] ?? 0),
        'address' => trim((string)($_POST['address'] ?? '')),
        'message' => trim((string)($_POST['message'] ?? '')),
    ];

    if (!verify_csrf()) {
        $result['errors']['csrf'] = 'Срок действия формы истёк. Обновите страницу и попробуйте снова.';
    }
    if ($spam = verify_antispam('repair_request', 5)) {
        $result['errors']['spam'] = $spam;
    }
    if (mb_strlen($data['name'], 'UTF-8') < 2) {
        $result['errors']['name'] = 'Укажите имя (минимум 2 символа).';
    }
    if (strlen(preg_replace('/\D/', '', $data['phone']) ?? '') < 10) {
        $result['errors']['phone'] = 'Укажите корректный номер телефона.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $result['errors']['email'] = 'Проверьте адрес электронной почты.';
    }
    if ($data['repair_type'] === '') {
        $result['errors']['repair_type'] = 'Выберите тип ремонта.';
    }
    if (empty($_POST['consent'])) {
        $result['errors']['consent'] = 'Необходимо согласие на обработку персональных данных.';
    }
    if (mb_strlen($data['message'], 'UTF-8') > 2000) {
        $result['errors']['message'] = 'Описание слишком длинное (максимум 2000 символов).';
    }

    if ($result['errors']) {
        return $result;
    }

    $stmt = db()->prepare(
        'INSERT INTO requests (user_id, name, phone, email, repair_type, area, budget, address, message, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'new\', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
    );
    $stmt->execute([
        $user['id'] ?? null,
        $data['name'],
        $data['phone'],
        $data['email'],
        $data['repair_type'],
        $data['area'],
        $data['budget'],
        $data['address'],
        $data['message'],
    ]);

    cache_clear();
    $requestId = (int)db()->lastInsertId();
    set_flash('success', 'Спасибо! Заявка №' . $requestId . ' принята. Менеджер свяжется с вами в течение рабочего дня.');
    redirect($redirectTo);
}

/**
 * @return array{errors: array<string,string>, done: bool}
 */
function process_review(?array $user, string $redirectTo = 'reviews.php'): array
{
    $result = ['errors' => [], 'done' => false];
    if (!is_post() || ($_POST['form'] ?? '') !== 'review') {
        return $result;
    }
    if (!$user) {
        $result['errors']['auth'] = 'Оставлять отзывы могут только авторизованные клиенты.';
        return $result;
    }

    $data = [
        'author_name' => trim((string)($_POST['author_name'] ?? $user['name'])),
        'city' => trim((string)($_POST['city'] ?? 'Москва')),
        'rating' => max(1, min(5, int_or_zero($_POST['rating'] ?? 5))),
        'service' => trim((string)($_POST['service'] ?? '')),
        'text' => trim((string)($_POST['text'] ?? '')),
    ];

    if (!verify_csrf()) {
        $result['errors']['csrf'] = 'Срок действия формы истёк. Обновите страницу.';
    }
    if ($spam = verify_antispam('review', 10)) {
        $result['errors']['spam'] = $spam;
    }
    if (mb_strlen($data['author_name'], 'UTF-8') < 2) {
        $result['errors']['author_name'] = 'Укажите имя.';
    }
    if (mb_strlen($data['text'], 'UTF-8') < 30) {
        $result['errors']['text'] = 'Расскажите подробнее — минимум 30 символов.';
    }
    if (mb_strlen($data['text'], 'UTF-8') > 1500) {
        $result['errors']['text'] = 'Отзыв слишком длинный (максимум 1500 символов).';
    }
    if (empty($_POST['consent'])) {
        $result['errors']['consent'] = 'Подтвердите согласие на публикацию отзыва.';
    }

    // Защита от повторной публикации подряд
    $stmt = db()->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND created_at > datetime('now', '-1 day')");
    $stmt->execute([$user['id']]);
    if ((int)$stmt->fetchColumn() > 0) {
        $result['errors']['limit'] = 'Вы уже отправляли отзыв сегодня. Новый можно оставить завтра.';
    }

    if ($result['errors']) {
        return $result;
    }

    $stmt = db()->prepare(
        'INSERT INTO reviews (user_id, author_name, city, rating, text, service, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, \'pending\', CURRENT_TIMESTAMP)'
    );
    $stmt->execute([$user['id'], $data['author_name'], $data['city'], $data['rating'], $data['text'], $data['service']]);

    cache_clear();
    set_flash('success', 'Спасибо за отзыв! Он появится на сайте после проверки модератором.');
    redirect($redirectTo);
}

/**
 * @return array{errors: array<string,string>, done: bool}
 */
function process_contact(string $redirectTo = 'contacts.php'): array
{
    $result = ['errors' => [], 'done' => false];
    if (!is_post() || ($_POST['form'] ?? '') !== 'contact') {
        return $result;
    }

    $data = [
        'name' => trim((string)($_POST['name'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'phone' => trim((string)($_POST['phone'] ?? '')),
        'subject' => trim((string)($_POST['subject'] ?? 'Обращение с сайта')),
        'message' => trim((string)($_POST['message'] ?? '')),
    ];

    if (!verify_csrf()) {
        $result['errors']['csrf'] = 'Срок действия формы истёк. Обновите страницу.';
    }
    if ($spam = verify_antispam('contact', 5)) {
        $result['errors']['spam'] = $spam;
    }
    if (mb_strlen($data['name'], 'UTF-8') < 2) {
        $result['errors']['name'] = 'Укажите имя.';
    }
    if ($data['email'] === '' && $data['phone'] === '') {
        $result['errors']['email'] = 'Оставьте e-mail или телефон для ответа.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $result['errors']['email'] = 'Проверьте адрес электронной почты.';
    }
    if (mb_strlen($data['message'], 'UTF-8') < 10) {
        $result['errors']['message'] = 'Сообщение должно содержать минимум 10 символов.';
    }
    if (empty($_POST['consent'])) {
        $result['errors']['consent'] = 'Необходимо согласие на обработку персональных данных.';
    }

    if ($result['errors']) {
        return $result;
    }

    $stmt = db()->prepare(
        'INSERT INTO contact_messages (name, email, phone, subject, message, is_read, created_at)
         VALUES (?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)'
    );
    $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['subject'] !== '' ? $data['subject'] : 'Обращение с сайта', $data['message']]);

    set_flash('success', 'Сообщение отправлено. Мы ответим вам в ближайшее время.');
    redirect($redirectTo);
}

/** Ссылка «назад» на текущую страницу без параметров */
function current_path(): string
{
    $path = strtok((string)($_SERVER['REQUEST_URI'] ?? '/'), '?');
    return ltrim((string)$path, '/');
}
