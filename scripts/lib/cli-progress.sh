#!/usr/bin/env bash
# Simple terminal progress helpers for project scripts.
# Usage: source "$(dirname "$0")/lib/cli-progress.sh"

if [[ -t 1 ]]; then
    _CLI_CLR_RESET=$'\033[0m'
    _CLI_CLR_DIM=$'\033[2m'
    _CLI_CLR_CYAN=$'\033[36m'
    _CLI_CLR_GREEN=$'\033[32m'
    _CLI_CLR_YELLOW=$'\033[33m'
    _CLI_CLR_RED=$'\033[31m'
else
    _CLI_CLR_RESET=
    _CLI_CLR_DIM=
    _CLI_CLR_CYAN=
    _CLI_CLR_GREEN=
    _CLI_CLR_YELLOW=
    _CLI_CLR_RED=
fi

_cli_spinner_pid=

cli_title() {
    echo "${_CLI_CLR_CYAN}==>${_CLI_CLR_RESET} $*"
}

cli_note() {
    echo "${_CLI_CLR_DIM}$*${_CLI_CLR_RESET}"
}

cli_done() {
    echo "${_CLI_CLR_GREEN}✓${_CLI_CLR_RESET} $*"
}

cli_fail() {
    echo "${_CLI_CLR_RED}✗${_CLI_CLR_RESET} $*" >&2
}

cli_progress_bar() {
    local percent="${1:-0}"
    local label="${2:-}"
    local width=40

    if (( percent < 0 )); then
        percent=0
    elif (( percent > 100 )); then
        percent=100
    fi

    local filled=$(( percent * width / 100 ))
    local empty=$(( width - filled ))
    local bar

    bar="$(printf '%*s' "$filled" '' | tr ' ' '█')"
    bar+="$(printf '%*s' "$empty" '' | tr ' ' '░')"

    printf '\r  [%s] %3d%% %s' "$bar" "$percent" "$label"
}

cli_spinner_start() {
    local message="${1:-Working...}"

    if ! [[ -t 1 ]]; then
        echo "$message"
        return
    fi

    (
        local frames='|/-\'
        local index=0

        while true; do
            printf '\r  %s %s' "${frames:index:1}" "$message"
            index=$(( (index + 1) % 4 ))
            sleep 0.12
        done
    ) &
    _cli_spinner_pid=$!
}

cli_spinner_stop() {
    local message="${1:-}"

    if [[ -n "${_cli_spinner_pid:-}" ]]; then
        kill "$_cli_spinner_pid" 2>/dev/null || true
        wait "$_cli_spinner_pid" 2>/dev/null || true
        _cli_spinner_pid=
        printf '\r\033[K'
    fi

    if [[ -n "$message" ]]; then
        cli_done "$message"
    fi
}

# Run rsync with a live overall progress line in the terminal.
# Usage: cli_rsync_with_progress <src/> <dst/> [rsync args...]
cli_rsync_with_progress() {
    local src="$1"
    local dst="$2"
    shift 2

    cli_title "Syncing"
    cli_note "$src"
    cli_note "  -> $dst"
    cli_note "Press Ctrl+C to cancel"
    echo ""

    rsync -a \
        --info=progress2 \
        --info=name1 \
        "$@" \
        "$src" \
        "$dst"

    local status=$?

    echo ""

    if (( status == 0 )); then
        cli_done "Sync complete"
    else
        cli_fail "Sync failed (exit $status)"
        return "$status"
    fi
}
