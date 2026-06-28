#!/usr/bin/env bash
# Run Vite with Linux Node (local) or Docker when WSL resolves Windows npm/node.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

is_windows_path() {
    [[ "${1:-}" == /mnt/c/* ]] || [[ "${1:-}" == /mnt/d/* ]]
}

node_path="$(command -v node 2>/dev/null || true)"
npm_path="$(command -v npm 2>/dev/null || true)"

use_docker=false

if [[ -z "$node_path" ]] || is_windows_path "$node_path" || is_windows_path "$npm_path"; then
    use_docker=true
elif [[ ! -x "$ROOT/node_modules/.bin/vite" ]]; then
    use_docker=true
fi

if [[ "$use_docker" == true ]]; then
    if ! command -v docker >/dev/null 2>&1; then
        echo "error: WSL is using Windows Node/npm, which cannot build from a Linux path." >&2
        echo "Fix: install Node in WSL (sudo apt install nodejs npm) or use Docker:" >&2
        echo "  ./scripts/build-frontend.sh" >&2
        exit 1
    fi

    args=("$@")
    if [[ ${#args[@]} -eq 0 ]]; then
        args=(build)
    fi

    quoted="$(printf '%q ' "${args[@]}")"
    exec docker compose --profile dev run --rm node sh -c "npm install && npx vite ${quoted}"
fi

exec "$ROOT/node_modules/.bin/vite" "$@"
