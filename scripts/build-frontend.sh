#!/usr/bin/env bash
# Build Vite assets from WSL (uses Docker when Windows npm is on PATH).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

bash "$ROOT/scripts/vite.sh" build
bash "$ROOT/scripts/node.sh" scripts/generate-nativephp-brand-assets.mjs

echo "Frontend build complete."
