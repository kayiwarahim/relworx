<?php

namespace KayiwaRahim\Relworx;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use KayiwaRahim\Relworx\Webhooks\RelworxWebhookVerifier;

class RelworxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/relworx.php', 'relworx');

        $this->app->singleton('relworx', function ($app) {
            return new RelworxManager($app['config']->get('relworx', []));
        });

        $this->app->singleton(RelworxWebhookVerifier::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/relworx.php' => config_path('relworx.php'),
        ], 'relworx-config');

        if (config('relworx.webhooks.enabled', false)) {
            Route::middleware(config('relworx.webhooks.middleware', ['api']))
                ->post(trim((string) config('relworx.webhooks.path', 'relworx/webhook'), '/'), \KayiwaRahim\Relworx\Http\Controllers\RelworxWebhookController::class)
                ->name('relworx.webhook');
        }
    }
}
