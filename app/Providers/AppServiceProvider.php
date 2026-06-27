<?php

namespace App\Providers;

use App\Models\Resource;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->seedMobileDatabaseWhenEmpty();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $key = $request->header('X-Device-Id') ?: $request->ip();

            return Limit::perMinute(600)->by($key);
        });

        RateLimiter::for('downloads', function (Request $request) {
            $key = $request->header('X-Device-Id') ?: $request->ip();

            return Limit::perMinute(120)->by($key);
        });
    }

    private function seedMobileDatabaseWhenEmpty(): void
    {
        if (! in_array(env('NATIVEPHP_PLATFORM'), ['android', 'ios'], true)) {
            return;
        }

        if (config('agora.mobile_api.enabled') && config('agora.mobile_api.base_url')) {
            return;
        }

        if (! Schema::hasTable('migrations')) {
            Artisan::call('migrate', ['--force' => true]);
        }

        if (! Schema::hasTable('resources')) {
            return;
        }

        if (Resource::query()->exists()) {
            if (Schema::hasTable('resource_files')) {
                Artisan::call('agora:ensure-sample-files');
            }

            return;
        }

        Artisan::call('db:seed', ['--force' => true]);
        Artisan::call('agora:ensure-sample-files');
    }
}
