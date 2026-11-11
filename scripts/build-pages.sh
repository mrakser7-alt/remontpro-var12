#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR="${PAGES_OUTPUT_DIR:-$ROOT_DIR/docs}"
PORT="${PAGES_BUILD_PORT:-8099}"
BASE_URL="http://127.0.0.1:${PORT}"
SERVER_LOG="$ROOT_DIR/data/pages-build.log"

rm -rf "$OUTPUT_DIR"
mkdir -p "$OUTPUT_DIR"

php -S "127.0.0.1:${PORT}" -t "$ROOT_DIR" >"$SERVER_LOG" 2>&1 &
SERVER_PID=$!
trap 'kill "$SERVER_PID" 2>/dev/null || true' EXIT

for _ in {1..30}; do
    if curl --fail --silent "$BASE_URL/index.php" >/dev/null; then
        break
    fi
    sleep 0.2
done

download() {
    local route="$1"
    local destination="$2"
    curl --fail --silent --show-error "$BASE_URL/$route" -o "$OUTPUT_DIR/$destination"
}

download "index.php" "index.html"
for page in about services portfolio reviews promos contacts team request login register; do
    download "${page}.php" "${page}.html"
done

while IFS= read -r slug; do
    download "service.php?slug=${slug}" "service-${slug}.html"
done < <(php -r '$pdo = new PDO("sqlite:" . $argv[1]); foreach ($pdo->query("SELECT slug FROM services WHERE is_active = 1 ORDER BY id") as $row) { echo $row["slug"], PHP_EOL; }' "$ROOT_DIR/data/database.sqlite")

while IFS= read -r id; do
    download "project.php?id=${id}" "project-${id}.html"
done < <(php -r '$pdo = new PDO("sqlite:" . $argv[1]); foreach ($pdo->query("SELECT id FROM portfolio ORDER BY id") as $row) { echo $row["id"], PHP_EOL; }' "$ROOT_DIR/data/database.sqlite")

cp -R "$ROOT_DIR/assets" "$OUTPUT_DIR/assets"
touch "$OUTPUT_DIR/.nojekyll"

php "$ROOT_DIR/scripts/rewrite-static.php" "$OUTPUT_DIR"

echo "Static site generated in $OUTPUT_DIR"

