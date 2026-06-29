#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

has_sqlite_driver() {
    php -r 'exit(in_array("sqlite", PDO::getAvailableDrivers(), true) ? 0 : 1);' 2>/dev/null
}

has_pgsql_driver() {
    php -r 'exit(in_array("pgsql", PDO::getAvailableDrivers(), true) ? 0 : 1);' 2>/dev/null
}

docker_app_running() {
    docker compose ps --status running app 2>/dev/null | grep -q 'AGORA_e-HUB-app'
}

ensure_docker_test_database() {
    docker compose exec -T postgres psql -U "${DB_USERNAME:-agora_ehub}" -tc \
        "SELECT 1 FROM pg_database WHERE datname = 'agora_ehub_testing'" \
        | grep -q 1 \
        || docker compose exec -T postgres psql -U "${DB_USERNAME:-agora_ehub}" -c \
            "CREATE DATABASE agora_ehub_testing"
}

run_local_tests() {
    php artisan config:clear --ansi >/dev/null 2>&1 || true
    exec php artisan test "$@"
}

if has_sqlite_driver || has_pgsql_driver; then
    run_local_tests "$@"
fi

if docker_app_running; then
    ensure_docker_test_database
    exec docker compose exec -T app php artisan test "$@"
fi

cat <<'EOF'
Unable to run tests: no PHP database driver is installed on the host,
and the Docker app container is not running.

Option A — install SQLite for local testing (recommended on WSL):
  sudo apt install php8.5-sqlite3

Option B — run tests in Docker:
  make up
  make test

EOF
exit 1
