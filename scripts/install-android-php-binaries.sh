#!/usr/bin/env bash
# Download NativePHP Android PHP binaries when Windows artisan install cannot reach bin.nativephp.com
set -euo pipefail

PHP_MINOR="${1:-8.4}"
PROJECT="${2:-/mnt/c/Users/domin/agora-ehub}"

case "$PHP_MINOR" in
    8.3) ZIP="android-3.1.0-php8.3.31.zip" ;;
    8.4) ZIP="android-3.1.0-php8.4.22.zip" ;;
    8.5) ZIP="android-3.1.0-php8.5.7.zip" ;;
    *) echo "Unsupported PHP version: $PHP_MINOR (use 8.3, 8.4, or 8.5)"; exit 1 ;;
esac

URL="https://bin.nativephp.com/main/${PHP_MINOR}/android/${ZIP}"
DEST="${PROJECT}/nativephp/android/app/src/main"
TMP="$(mktemp -d)"

echo "Downloading $URL"
curl -fSL -o "$TMP/php-android.zip" "$URL"
unzip -q "$TMP/php-android.zip" -d "$TMP/out"

mkdir -p "$DEST"
rm -rf "$DEST/staticLibs" "$DEST/include"
cp -r "$TMP/out/staticLibs" "$TMP/out/include" "$DEST/"
rm -rf "$TMP"

if [[ ! -f "$DEST/staticLibs/arm64-v8a/libphp.a" ]]; then
    echo "ERROR: libphp.a not found after extract"
    exit 1
fi

cat > "${PROJECT}/nativephp.lock" <<EOF
{
    "php": {
        "version": "$(echo "$ZIP" | sed -E 's/.*php([0-9.]+)\.zip/\1/')",
        "icu": false
    }
}
EOF

echo "Installed PHP $PHP_MINOR binaries to $DEST"
echo "Next (Windows PowerShell): .\\scripts\\native-run-android.cmd"
