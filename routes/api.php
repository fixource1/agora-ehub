<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContributorResourceController;
use App\Http\Controllers\Api\V1\DownloadController;
use App\Http\Controllers\Api\V1\LookupController;
use App\Http\Controllers\Api\V1\ResourceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('resources', [ResourceController::class, 'index']);
    Route::get('resources/{resource:slug}', [ResourceController::class, 'show']);

    Route::get('downloads', [DownloadController::class, 'index']);
    Route::post('downloads', [DownloadController::class, 'store']);
    Route::delete('downloads/{resource:slug}', [DownloadController::class, 'destroy']);

    Route::get('lookups/categories', [LookupController::class, 'categories']);
    Route::get('lookups/resource-types', [LookupController::class, 'resourceTypes']);

    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/user', [AuthController::class, 'user']);

        Route::get('my/resources', [ContributorResourceController::class, 'index']);
        Route::post('my/resources', [ContributorResourceController::class, 'store']);
        Route::get('my/resources/{resource:slug}', [ContributorResourceController::class, 'show']);
        Route::match(['put', 'patch'], 'my/resources/{resource:slug}', [ContributorResourceController::class, 'update']);
        Route::delete('my/resources/{resource:slug}', [ContributorResourceController::class, 'destroy']);
    });
});
