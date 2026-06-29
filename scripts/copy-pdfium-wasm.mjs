import { copyFileSync, mkdirSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const source = path.join(root, 'node_modules/@embedpdf/pdfium/dist/pdfium.wasm');
const destinationDir = path.join(root, 'public/pdf');
const destination = path.join(destinationDir, 'pdfium.wasm');

mkdirSync(destinationDir, { recursive: true });
copyFileSync(source, destination);

console.log(`Copied pdfium.wasm to ${path.relative(root, destination)}`);
