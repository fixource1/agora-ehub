#!/usr/bin/env bash
# Sync WSL project to Windows copy for NativePHP Android builds.
# Usage: ./scripts/sync-to-windows.sh [--quick]
#
# --quick  Skip large folders (vendor/, nativephp/) when they already exist on Windows.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib/cli-progress.sh
source "$SCRIPT_DIR/lib/cli-progress.sh"

SRC="$(cd "$SCRIPT_DIR/.." && pwd)"
DST="/mnt/c/Users/domin/agora-ehub"
QUICK=false

for arg in "$@"; do
    case "$arg" in
        --quick)
            QUICK=true
            ;;
        -h|--help)
            echo "Usage: $0 [--quick]"
            echo ""
            echo "  --quick  Skip vendor/ and nativephp/ (faster for small code changes)"
            exit 0
            ;;
        *)
            cli_fail "Unknown option: $arg"
            echo "Run: $0 --help"
            exit 1
            ;;
    esac
done

if [[ ! -d "$DST" ]]; then
    cli_note "Creating $DST"
    mkdir -p "$DST"
fi

RSYNC_ARGS=(
    --delete
    --exclude='.env'
    --exclude='public/storage'
    --exclude='node_modules'
    --exclude='.git'
    --exclude='storage/framework/cache/data/*'
    --exclude='storage/framework/sessions/*'
    --exclude='storage/framework/views/*'
    --exclude='storage/logs/*.log'
)

if [[ "$QUICK" == true ]]; then
    RSYNC_ARGS+=(
        --exclude='vendor/'
        --exclude='nativephp/'
    )
    cli_note "Quick mode: skipping vendor/ and nativephp/"
    echo ""
fi

cli_rsync_with_progress "$SRC/" "$DST/" "${RSYNC_ARGS[@]}"

echo ""
cli_done "Build from: C:\\Users\\domin\\agora-ehub"
cli_note "Windows .env is NOT synced — edit config/nativephp.env for mobile API settings"
