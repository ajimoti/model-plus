<?php

declare(strict_types=1);

namespace Vendor\ModelPlus;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Container\Container;
use Vendor\ModelPlus\Services\ModelDiscoveryService;

final class ModelPlusServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/modelplus.php', 'modelplus');

        // Get middleware configuration
        $middleware = Config::get('modelplus.route.middleware', ['web']);
        if (Config::get('modelplus.route.require_auth', false)) {
            $middleware[] = 'auth';
        }

        Config::set('modelplus.route.middleware', $middleware);

        $this->app->singleton(ModelDiscoveryService::class, function (Container $app) {
            return new ModelDiscoveryService(
                Config::get('modelplus.model_paths', [App::path('Models')])
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/modelplus.php' => config_path('modelplus.php'),
                __DIR__ . '/../resources/views' => resource_path('views/vendor/modelplus'),
            ], 'modelplus-config');
        }

        Route::middleware(Config::get('modelplus.route.middleware'))
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });

        View::addNamespace('modelplus', __DIR__ . '/../resources/views');
    }
} 