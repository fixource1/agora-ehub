import { existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const requiredPackages = [
    '@embedpdf/core',
    '@embedpdf/engines',
    '@embedpdf/models',
    '@embedpdf/pdfium',
    '@embedpdf/plugin-document-manager',
    '@embedpdf/plugin-render',
    '@embedpdf/plugin-scroll',
    '@embedpdf/plugin-viewport',
    '@embedpdf/plugin-zoom',
];

const missing = requiredPackages.filter(
    (name) => ! existsSync(path.join(root, 'node_modules', name, 'package.json')),
);

if (missing.length > 0) {
    console.error('Missing npm packages required for the PDF reader build:');
    for (const name of missing) {
        console.error(`  - ${name}`);
    }
    console.error('');
    console.error('Run: npm install');
    process.exit(1);
}
