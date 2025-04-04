<?php

namespace Apih\JsLang;

use Apih\JsLang\Commands\ClearCommand;
use Apih\JsLang\Commands\GenerateCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class JsLangServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge the config
        $this->mergeConfigFrom(__DIR__ . '/../config/jslang.php', 'jslang');

        // Register paths for publish
        $this->publishes([
            __DIR__ . '/../config/jslang.php' => config_path('jslang.php'),
        ], 'jslang-config');

        $this->publishes([
            __DIR__ . '/../resources/js/lang.js' => resource_path('js/lang.js'),
        ], 'jslang-script');

        // Register the service
        $service = function ($app) {
            return new JsLang($app->get(Filesystem::class));
        };

        if (config('jslang.scoped_singleton')) {
            $this->app->scoped(JsLang::class, $service);
        } else {
            $this->app->singleton(JsLang::class, $service);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateCommand::class,
                ClearCommand::class,
            ]);
        }

        // Register the route
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}
