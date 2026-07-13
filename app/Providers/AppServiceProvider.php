<?php

namespace App\Providers;

use App\Models\SchoolSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrapFive();

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        if ($this->app->environment('production') || config('app.force_https')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $settings = null;

            try {
                if (Schema::hasTable('school_settings')) {
                    $settings = SchoolSetting::current();
                }
            } catch (\Throwable $exception) {
                $settings = null;
            }

            $view->with('schoolSettings', $settings);
        });
    }
}
