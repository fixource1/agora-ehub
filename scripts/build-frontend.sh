#!/usr/bin/env bash
# Build Vite assets from WSL (uses Docker when Windows npm is on PATH).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -d "$ROOT/public/build" ]] && ! [[ -w "$ROOT/public/build" ]]; then
    echo "public/build is not writable — fixing permissions…"
    bash "$ROOT/scripts/fix-frontend-permissions.sh"
fi

bash "$ROOT/scripts/vite.sh" build
bash "$ROOT/scripts/node.sh" scripts/copy-pdfium-wasm.mjs
bash "$ROOT/scripts/node.sh" scripts/copy-pdfjs-worker.mjs
bash "$ROOT/scripts/node.sh" scripts/generate-nativephp-brand-assets.mjs

echo "Frontend build complete."
