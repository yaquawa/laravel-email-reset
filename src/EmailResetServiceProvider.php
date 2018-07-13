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

        $this->registerRoute();
    }

    /**
     * Register Passport's migration files.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (Config::defaultDriverConfig('ignore-migrations')) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function publishAssets()
    {
        $this->publishes([
            __DIR__ . '/Http/Controllers' => base_path('app/Http/Controllers/Auth'),
        ], 'email-reset-controllers');
    }

    public function registerRoute()
    {
        if ( ! $this->app->routesAreCached()) {
            $route = Config::defaultDriverConfig('route') ?? 'email/reset/{token}';

            Route::middleware('auth')->get($route, 'App\Http\Controllers\Controller\ResetEmailController@reset')->name('email-reset');
        }

    }
}
