<?php
/**
 * Конфигурация приложения, подключение к БД SQLite,
 * сессии, CSRF-защита, контроль доступа по ролям и кеширование.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Заголовки безопасности и производительности
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

define('BASE_PATH', dirname(__DIR__));
define('DB_PATH', BASE_PATH . '/data/database.sqlite');
define('CACHE_DIR', BASE_PATH . '/data/cache');

/**
 * Базовый веб-путь проекта (работает и в подкаталоге, и из /admin)
 */
$__scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
if (basename($__scriptDir) === 'admin') {
    $__scriptDir = str_replace('\\', '/', dirname($__scriptDir));
}
define('WEB_ROOT', ($__scriptDir === '/' || $__scriptDir === '\\') ? '' : rtrim($__scriptDir, '/'));
unset($__scriptDir);

/** Абсолютный URL внутри проекта */
function url(string $path = ''): string {
    return WEB_ROOT . '/' . ltrim($path, '/');
}

function redirect(string $path): never {
    header('Location: ' . url($path));
    exit;
}

if (!is_dir(dirname(DB_PATH))) {
    mkdir(dirname(DB_PATH), 0775, true);
}
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0775, true);
}

/**
 * Получение экземпляра PDO (Singleton)
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $isNew = !file_exists(DB_PATH) || filesize(DB_PATH) === 0;
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec('PRAGMA journal_mode = WAL;');

        if ($isNew) {
            require_once __DIR__ . '/seed.php';
            initDatabase($pdo);
        } else {
            // Проверка наличия таблиц на случай пустого файла
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
            if (!$stmt->fetch()) {
                require_once __DIR__ . '/seed.php';
                initDatabase($pdo);
            }
        }
    }
    return $pdo;
}

/**
 * Экранирование вывода HTML
 */
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Генерация и проверка CSRF-токена
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Антиспам проверка (Honeypot + лимит частоты отправки форм)
 */
function verify_antispam(string $formKey = 'default', int $cooldownSeconds = 3): ?string {
    // Honeypot поле (должно оставаться пустым)
    if (!empty($_POST['website_url_hp'])) {
        return 'Обнаружена подозрительная активность (спам-фильтр).';
    }
    $lastSubmitKey = 'last_submit_' . $formKey;
    $now = time();
    if (isset($_SESSION[$lastSubmitKey]) && ($now - $_SESSION[$lastSubmitKey]) < $cooldownSeconds) {
        return 'Пожалуйста, подождите несколько секунд перед повторной отправкой формы.';
    }
    $_SESSION[$lastSubmitKey] = $now;
    return null;
}

/**
 * Текущий авторизованный пользователь
 */
function current_user(): ?array {
    static $user = null;
    static $loaded = false;

    if (!$loaded) {
        $loaded = true;
        if (!empty($_SESSION['user_id'])) {
            $stmt = db()->prepare('SELECT id, name, email, phone, role, avatar_color, created_at FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch() ?: null;
            if (!$user) {
                unset($_SESSION['user_id']);
            }
        }
    }
    return $user;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function has_role(string|array $roles): bool {
    $user = current_user();
    if (!$user) {
        return false;
    }
    $roles = (array)$roles;
    return in_array($user['role'], $roles, true);
}

function require_login(string $redirect = 'cabinet.php'): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Пожалуйста, войдите в аккаунт для доступа к этой странице.');
        header('Location: ' . $redirect);
        exit;
    }
}

function require_role(string|array $roles, string $redirect = 'index.php'): void {
    require_login();
    if (!has_role($roles)) {
        set_flash('error', 'Недостаточно прав для доступа к разделу.');
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Flash-уведомления
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/**
 * Файловое кеширование данных для повышения производительности
 */
function cache_remember(string $key, int $ttl, callable $callback): mixed {
    $file = CACHE_DIR . '/' . md5($key) . '.cache';
    if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
        $content = @file_get_contents($file);
        if ($content !== false) {
            $data = @unserialize($content);
            if ($data !== false || $content === serialize(false)) {
                return $data;
            }
        }
    }
    $data = $callback();
    @file_put_contents($file, serialize($data), LOCK_EX);
    return $data;
}

function cache_clear(): void {
    foreach (glob(CACHE_DIR . '/*.cache') ?: [] as $file) {
        @unlink($file);
    }
}

/**
 * Форматирование цены и дат
 */
function format_price(int|float $price): string {
    return number_format((float)$price, 0, ',', ' ') . ' ₽';
}

function format_date(?string $dateStr): string {
    if (!$dateStr) return '';
    $ts = strtotime($dateStr);
    if (!$ts) return $dateStr;
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    ];
    $d = (int)date('j', $ts);
    $m = $months[(int)date('n', $ts)];
    $y = date('Y', $ts);
    return "$d $m $y";
}

function role_badge(string $role): array {
    return match ($role) {
        'admin' => ['label' => 'Администратор', 'class' => 'badge-admin'],
        'manager' => ['label' => 'Менеджер', 'class' => 'badge-manager'],
        default => ['label' => 'Клиент', 'class' => 'badge-user'],
    };
}

function status_badge(string $status): array {
    return match ($status) {
        'new' => ['label' => 'Новая заявка', 'class' => 'status-new'],
        'measurement' => ['label' => 'Замер и смета', 'class' => 'status-measurement'],
        'in_progress' => ['label' => 'В работе', 'class' => 'status-progress'],
        'completed' => ['label' => 'Выполнено', 'class' => 'status-completed'],
        'cancelled' => ['label' => 'Отменена', 'class' => 'status-cancelled'],
        default => ['label' => $status, 'class' => 'status-new'],
    };
}

function review_status_badge(string $status): array {
    return match ($status) {
        'approved' => ['label' => 'Опубликован', 'class' => 'status-completed'],
        'rejected' => ['label' => 'Отклонён', 'class' => 'status-cancelled'],
        default => ['label' => 'На модерации', 'class' => 'status-measurement'],
    };
}

function initials(string $name): string {
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $out .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
    }
    return $out !== '' ? $out : '?';
}

function render_stars(int $rating): string {
    $rating = max(0, min(5, $rating));
    $html = '<span class="stars" aria-label="Оценка ' . $rating . ' из 5">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<svg class="star' . ($i <= $rating ? ' star--on' : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.5 6.1 20.6l1.2-6.5L2.5 9.5l6.6-.9L12 2.6z"/></svg>';
    }
    return $html . '</span>';
}

function truncate_text(string $text, int $limit = 140): string {
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . '…';
}

function make_slug(string $text): string {
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
        'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];
    $slug = mb_strtolower($text, 'UTF-8');
    $slug = strtr($slug, $map);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : 'item-' . time();
}

function unique_slug(PDO $pdo, string $table, string $base, ?int $ignoreId = null): string {
    $slug = $base;
    $i = 1;
    while (true) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE slug = ?";
        $params = [$slug];
        if ($ignoreId) {
            $sql .= ' AND id != ?';
            $params[] = $ignoreId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (!(int)$stmt->fetchColumn()) {
            return $slug;
        }
        $slug = $base . '-' . (++$i);
    }
}

function is_post(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function int_or_zero(mixed $value): int {
    return (int)preg_replace('/\D+/', '', (string)$value);
}

function repair_categories(): array {
    return ['Косметический ремонт', 'Капитальный ремонт', 'Дизайнерский ремонт', 'Отдельные работы', 'Инженерные работы'];
}

function portfolio_categories(): array {
    return ['Квартиры', 'Коттеджи', 'Ванные комнаты', 'Кухни'];
}

