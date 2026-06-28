#!/usr/bin/env bash
# Run Node scripts with Linux Node or Docker (avoids Windows npm UNC path errors in WSL).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ $# -lt 1 ]]; then
    echo "usage: $0 <script> [args...]" >&2
    exit 1
fi

is_windows_path() {
    [[ "${1:-}" == /mnt/c/* ]] || [[ "${1:-}" == /mnt/d/* ]]
}

node_path="$(command -v node 2>/dev/null || true)"

if [[ -z "$node_path" ]] || is_windows_path "$node_path"; then
    if ! command -v docker >/dev/null 2>&1; then
        echo "error: WSL is using Windows Node, which cannot run scripts from a Linux path." >&2
        echo "Fix: install Node in WSL or use ./scripts/build-frontend.sh" >&2
        exit 1
    fi

    quoted="$(printf '%q ' "$@")"
    exec docker compose --profile dev run --rm node sh -c "npm install && node ${quoted}"
fi

exec node "$@"
