<?php

namespace Apih\JsLang;

use Apih\JsLang\Commands\ClearCommand;
use Apih\JsLang\Commands\GenerateCommand;
use Illuminate\Contracts\Foundation\Application;
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

        // Register the service
        $this->app->{config('jslang.scoped_singleton') ? 'scoped' : 'singleton'}(
            JsLang::class,
            static fn (Application $app) => new JsLang($app->get(Filesystem::class))
        );
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

            // Register paths for publishable files
            $this->publishes([
                __DIR__ . '/../config/jslang.php' => config_path('jslang.php'),
            ], 'jslang-config');

            $this->publishes([
                __DIR__ . '/../resources/js/lang.js' => resource_path('js/lang.js'),
            ], 'jslang-script');
        }

        // Register the route
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}
