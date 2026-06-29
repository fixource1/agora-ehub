<?php

use App\Http\Controllers\Api\V1\AdminCategoryController;
use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContributorResourceController;
use App\Http\Controllers\Api\V1\DownloadController;
use App\Http\Controllers\Api\V1\LookupController;
use App\Http\Controllers\Api\V1\ResourceController;
use App\Http\Controllers\Api\V1\ResourceFileDownloadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('resources', [ResourceController::class, 'index']);
    Route::get('resources/{resource:slug}', [ResourceController::class, 'show']);
    Route::get('resources/{resource:slug}/files/{resourceFile}/download', ResourceFileDownloadController::class)
        ->middleware('throttle:downloads');

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
        Route::match(['put', 'patch', 'post'], 'my/resources/{resource:slug}', [ContributorResourceController::class, 'update']);
        Route::delete('my/resources/{resource:slug}', [ContributorResourceController::class, 'destroy']);

        Route::middleware('can:manage users')->prefix('admin/users')->group(function () {
            Route::get('/', [AdminUserController::class, 'index']);
            Route::post('/', [AdminUserController::class, 'store']);
            Route::get('{user}', [AdminUserController::class, 'show']);
            Route::match(['put', 'patch'], '{user}', [AdminUserController::class, 'update']);
            Route::delete('{user}', [AdminUserController::class, 'destroy']);
        });

        Route::middleware('can:manage categories')->prefix('admin/categories')->group(function () {
            Route::get('/', [AdminCategoryController::class, 'index']);
            Route::post('/', [AdminCategoryController::class, 'store']);
            Route::get('{category}', [AdminCategoryController::class, 'show']);
            Route::match(['put', 'patch'], '{category}', [AdminCategoryController::class, 'update']);
            Route::delete('{category}', [AdminCategoryController::class, 'destroy']);
        });
    });
});
