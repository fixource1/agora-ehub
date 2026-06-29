<?php

use Illuminate\Support\Facades\Route;

Route::get('/pdf/pdf.worker.min.mjs', function () {
    $path = public_path('pdf/pdf.worker.min.mjs');

    abort_unless(is_readable($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->name('pdfjs.worker');

Route::get('/pdf/pdfium.wasm', function () {
    $path = public_path('pdf/pdfium.wasm');

    abort_unless(is_readable($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/wasm',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->name('pdfium.wasm');

Route::view('/author/{any?}', 'web')->where('any', '.*');

Route::view('/{any?}', 'app')->where('any', '^(?!admin|api|up|author|build|brand|storage|pdf|js|fonts|hot).*$');
