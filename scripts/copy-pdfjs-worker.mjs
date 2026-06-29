import { copyFileSync, mkdirSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const source = path.join(root, 'node_modules/pdfjs-dist/legacy/build/pdf.worker.min.mjs');
const destinationDir = path.join(root, 'public/pdf');
const destination = path.join(destinationDir, 'pdf.worker.min.mjs');

mkdirSync(destinationDir, { recursive: true });
copyFileSync(source, destination);

console.log(`Copied pdf.worker.min.mjs to ${path.relative(root, destination)}`);
