import { mkdir, rm, unlink, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const brandDir = join(root, 'public', 'brand');
const generatedDir = join(brandDir, 'generated');
const androidResDir = join(root, 'nativephp', 'android', 'app', 'src', 'main', 'res');

const iconBackground = '#ffffff';
const splashLightBackground = '#ffffff';
const splashDarkBackground = '#000000';
const splashContentScale = 0.82;
const iconSvg = join(brandDir, 'agora-icon.svg');
const splashLightSrc = join(brandDir, 'agora_splash_light.png');
const splashDarkSrc = join(brandDir, 'agora_splash_dark.png');

const archiveIconOut = join(generatedDir, 'icon-1024.png');

const launcherSizes = {
    'mipmap-mdpi': 48,
    'mipmap-hdpi': 72,
    'mipmap-xhdpi': 96,
    'mipmap-xxhdpi': 144,
    'mipmap-xxxhdpi': 192,
};

const adaptiveForegroundSizes = {
    'mipmap-mdpi': 108,
    'mipmap-hdpi': 162,
    'mipmap-xhdpi': 216,
    'mipmap-xxhdpi': 324,
    'mipmap-xxxhdpi': 432,
};

const portraitSplashSizes = {
    mdpi: [320, 480],
    hdpi: [480, 720],
    xhdpi: [640, 960],
    xxhdpi: [960, 1440],
    xxxhdpi: [1280, 1920],
};

const landscapeSplashSizes = {
    mdpi: [800, 534],
    hdpi: [1200, 800],
    xhdpi: [1600, 1067],
    xxhdpi: [2400, 1600],
    xxxhdpi: [3200, 2133],
};

let sharp;

try {
    sharp = (await import('sharp')).default;
} catch {
    console.error('Missing dev dependency "sharp". Run: npm install');
    process.exit(1);
}

const iconSize = 1024;

await mkdir(generatedDir, { recursive: true });

const iconBuffer = await sharp(iconSvg, { density: 360 })
    .resize(iconSize, iconSize, {
        fit: 'contain',
        background: iconBackground,
    })
    .flatten({ background: iconBackground })
    .png()
    .toBuffer();

await writeFile(archiveIconOut, iconBuffer);

async function ensureDir(path) {
    await mkdir(path, { recursive: true });
}

async function removeLegacyLauncherWebp(targetDir) {
    const legacyNames = [
        'ic_launcher.webp',
        'ic_launcher_round.webp',
        'ic_launcher_foreground.webp',
    ];

    for (const name of legacyNames) {
        try {
            await unlink(join(targetDir, name));
        } catch (error) {
            if (error.code !== 'ENOENT') {
                throw error;
            }
        }
    }
}

async function removeLegacySplashAssets() {
    const densities = Object.keys(portraitSplashSizes);
    const legacyDirs = [];

    for (const density of densities) {
        legacyDirs.push(
            join(androidResDir, `drawable-${density}`),
            join(androidResDir, `drawable-night-${density}`),
            join(androidResDir, `drawable-night-port-${density}`),
            join(androidResDir, `drawable-night-land-${density}`),
        );
    }

    for (const dir of legacyDirs) {
        try {
            await unlink(join(dir, 'splash.png'));
        } catch (error) {
            if (error.code !== 'ENOENT') {
                throw error;
            }
        }

        try {
            await rm(dir, { recursive: true, force: true });
        } catch (error) {
            if (error.code !== 'ENOENT') {
                throw error;
            }
        }
    }
}

async function writeResizedPng(source, destination, width, height, options = {}) {
    const { foreground = false } = options;
    const inset = foreground ? 0.69 : 1;
    const target = Math.round(Math.min(width, height) * inset);
    const background = foreground ? { r: 0, g: 0, b: 0, alpha: 0 } : iconBackground;

    const buffer = await sharp(source)
        .resize(target, target, {
            fit: 'contain',
            background,
        })
        .extend({
            top: Math.floor((height - target) / 2),
            bottom: Math.ceil((height - target) / 2),
            left: Math.floor((width - target) / 2),
            right: Math.ceil((width - target) / 2),
            background,
        })
        .png()
        .toBuffer();

    await writeFile(destination, buffer);
}

async function writeSplashVariant(source, destination, width, height, background) {
    const horizontalPadding = Math.round(width * ((1 - splashContentScale) / 2));
    const verticalPadding = Math.round(height * ((1 - splashContentScale) / 2));
    const contentWidth = width - (horizontalPadding * 2);
    const contentHeight = height - (verticalPadding * 2);

    const logo = await sharp(source)
        .ensureAlpha()
        .toColorspace('srgb')
        .resize(contentWidth, contentHeight, {
            fit: 'inside',
            withoutEnlargement: true,
            kernel: sharp.kernel.lanczos3,
            background: { r: 0, g: 0, b: 0, alpha: 0 },
        })
        .png({ compressionLevel: 6, palette: false })
        .toBuffer();

    await sharp({
        create: {
            width,
            height,
            channels: 4,
            background: { ...hexToRgb(background), alpha: 1 },
        },
    })
        .composite([{ input: logo, gravity: 'center' }])
        .flatten({ background })
        .png({ compressionLevel: 6, palette: false })
        .toFile(destination);
}

function hexToRgb(hex) {
    const normalized = hex.replace('#', '');

    return {
        r: Number.parseInt(normalized.slice(0, 2), 16),
        g: Number.parseInt(normalized.slice(2, 4), 16),
        b: Number.parseInt(normalized.slice(4, 6), 16),
    };
}

for (const [folder, size] of Object.entries(launcherSizes)) {
    const targetDir = join(androidResDir, folder);
    await ensureDir(targetDir);
    await removeLegacyLauncherWebp(targetDir);

    await writeResizedPng(iconBuffer, join(targetDir, 'ic_launcher.png'), size, size);
    await writeResizedPng(iconBuffer, join(targetDir, 'ic_launcher_round.png'), size, size);

    const foregroundSize = adaptiveForegroundSizes[folder];
    await writeResizedPng(
        iconBuffer,
        join(targetDir, 'ic_launcher_foreground.png'),
        foregroundSize,
        foregroundSize,
        { foreground: true },
    );
}

await removeLegacySplashAssets();

for (const [density, [portraitWidth, portraitHeight]] of Object.entries(portraitSplashSizes)) {
    const [landscapeWidth, landscapeHeight] = landscapeSplashSizes[density];

    const lightPortraitDir = join(androidResDir, `drawable-port-${density}`);
    const lightLandscapeDir = join(androidResDir, `drawable-land-${density}`);
    const darkPortraitDir = join(androidResDir, `drawable-port-night-${density}`);
    const darkLandscapeDir = join(androidResDir, `drawable-land-night-${density}`);

    await ensureDir(lightPortraitDir);
    await ensureDir(lightLandscapeDir);
    await ensureDir(darkPortraitDir);
    await ensureDir(darkLandscapeDir);

    await writeSplashVariant(
        splashLightSrc,
        join(lightPortraitDir, 'splash.png'),
        portraitWidth,
        portraitHeight,
        splashLightBackground,
    );
    await writeSplashVariant(
        splashLightSrc,
        join(lightLandscapeDir, 'splash.png'),
        landscapeWidth,
        landscapeHeight,
        splashLightBackground,
    );
    await writeSplashVariant(
        splashDarkSrc,
        join(darkPortraitDir, 'splash.png'),
        portraitWidth,
        portraitHeight,
        splashDarkBackground,
    );
    await writeSplashVariant(
        splashDarkSrc,
        join(darkLandscapeDir, 'splash.png'),
        landscapeWidth,
        landscapeHeight,
        splashDarkBackground,
    );
}

console.log('Generated NativePHP Android brand assets:');
console.log(`  Launcher icon background: ${iconBackground}`);
console.log(`  Light splash background: ${splashLightBackground}`);
console.log(`  Dark splash background: ${splashDarkBackground}`);
console.log(`  Splash logo scale: ${Math.round(splashContentScale * 100)}%`);
console.log(`  Splash light source: ${splashLightSrc}`);
console.log(`  Splash dark source: ${splashDarkSrc}`);
console.log(`  ${androidResDir}`);
console.log(`  Archive icon: ${archiveIconOut}`);
