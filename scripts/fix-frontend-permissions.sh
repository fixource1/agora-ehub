#!/usr/bin/env bash
# Fix public/build and node_modules ownership after Docker npm/vite wrote as nobody/root.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

UID_NUM="${UID:-1000}"
GID_NUM="${GID:-1000}"

paths=(
    public/build
    node_modules
    nativephp/android/app/src/main/res
)

existing=()
for path in "${paths[@]}"; do
    if [[ -e "$path" ]]; then
        existing+=("/var/www/html/$path")
    fi
done

if [[ ${#existing[@]} -eq 0 ]]; then
    echo "Nothing to fix."
    exit 0
fi

if ! command -v docker >/dev/null 2>&1; then
    echo "error: Docker is required, or run: sudo chown -R \$USER:\$USER public/build node_modules" >&2
    exit 1
fi

quoted_paths="$(printf '%q ' "${existing[@]}")"

echo "Fixing ownership to ${UID_NUM}:${GID_NUM} for frontend build paths…"
docker compose exec -T -u root app sh -c "chown -R ${UID_NUM}:${GID_NUM} ${quoted_paths}"
echo "Done. You can run: npm run build"
