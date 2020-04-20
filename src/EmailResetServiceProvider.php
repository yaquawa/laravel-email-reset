<?php

namespace Yaquawa\Laravel\EmailReset;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EmailResetServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
            $this->publishAssets();
        }

        $this->registerRoutes();
        $this->registerTranslations();
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'laravel-email-reset');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
    }

    /**
     * Register Passport's migration files.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        if (Config::defaultDriverConfig('ignore-migrations')) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/Http/Controllers' => base_path('app/Http/Controllers/Auth'),
        ], 'laravel-email-reset');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/laravel-email-reset'),
        ], 'laravel-email-reset');
    }

    public function registerRoutes(): void
    {
        if ( ! $this->app->routesAreCached()) {
            $route = Config::defaultDriverConfig('route') ?? 'email/reset/{token}';

            Route::middleware(['web', 'auth'])->get($route, Config::defaultDriverConfig('callback'))->name('email-reset');
        }

    }
}
