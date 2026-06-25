<?php

use Illuminate\Support\Facades\Route;

Route::view('/author/{any?}', 'web')->where('any', '.*');

Route::view('/{any?}', 'app')->where('any', '^(?!admin|api|up|author).*$');
