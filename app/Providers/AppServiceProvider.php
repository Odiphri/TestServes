<?php

namespace App\Providers;

use App\Models\SchoolSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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
