<?php
declare(strict_types=1);

$outputDir = $argv[1] ?? '';
if ($outputDir === '' || !is_dir($outputDir)) {
    fwrite(STDERR, "Usage: php scripts/rewrite-static.php <output-directory>\n");
    exit(1);
}

$banner = <<<'HTML'
<div class="static-demo-banner" role="status">
    <strong>Демо на GitHub Pages.</strong>
    Формы, регистрация, личный кабинет и админ-панель работают в полной PHP-версии после локального запуска.
</div>
HTML;

$styles = <<<'HTML'
<style>
.static-demo-banner{position:relative;z-index:1000;padding:10px 20px;background:#17212b;color:#fff;text-align:center;font:600 13px/1.4 Manrope,Arial,sans-serif}.static-demo-banner strong{color:#f0ad4e}.static-demo-banner a{color:#fff;text-decoration:underline}
</style>
HTML;

$script = <<<'HTML'
<script>
document.addEventListener('submit', function (event) {
    event.preventDefault();
    window.alert('Это статическая демо-версия. Запустите PHP-версию из GitHub-репозитория, чтобы отправлять формы и пользоваться аккаунтами.');
});
</script>
HTML;

function staticUrl(string $url): string
{
    $decoded = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $decoded = ltrim($decoded, '/');

    if ($decoded === '' || str_starts_with($decoded, '#') || preg_match('~^(?:https?:|mailto:|tel:|data:)~i', $decoded)) {
        return $url;
    }

    $fragment = '';
    if (($position = strpos($decoded, '#')) !== false) {
        $fragment = substr($decoded, $position);
        $decoded = substr($decoded, 0, $position);
    }

    if (preg_match('~^service\.php\?slug=([^&]+)~', $decoded, $matches)) {
        return 'service-' . rawurlencode(rawurldecode($matches[1])) . '.html' . $fragment;
    }
    if (preg_match('~^project\.php\?id=(\d+)~', $decoded, $matches)) {
        return 'project-' . $matches[1] . '.html' . $fragment;
    }
    if (str_starts_with($decoded, 'admin/') || str_starts_with($decoded, 'cabinet.php') || str_starts_with($decoded, 'logout.php')) {
        return 'login.html' . $fragment;
    }

    $path = strtok($decoded, '?') ?: $decoded;
    if ($path === 'index.php') {
        return 'index.html' . $fragment;
    }
    if (preg_match('~^([a-z-]+)\.php$~i', $path, $matches)) {
        return $matches[1] . '.html' . $fragment;
    }

    return $decoded . $fragment;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($outputDir, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'html') {
        continue;
    }

    $html = file_get_contents($file->getPathname());
    if ($html === false) {
        throw new RuntimeException('Cannot read ' . $file->getPathname());
    }

    $html = preg_replace_callback(
        '~\b(href|src|action)=([' . "'\"" . '])([^' . "'\"" . ']*)\2~i',
        static function (array $matches): string {
            $url = staticUrl($matches[3]);
            return $matches[1] . '=' . $matches[2] . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . $matches[2];
        },
        $html
    );

    $html = preg_replace('~<head>~i', "<head>\n" . $styles, $html, 1);
    $html = preg_replace('~<body([^>]*)>~i', '<body$1>' . "\n" . $banner, $html, 1);
    $html = preg_replace('~</body>~i', $script . "\n</body>", $html, 1);

    if (file_put_contents($file->getPathname(), $html) === false) {
        throw new RuntimeException('Cannot write ' . $file->getPathname());
    }
}

