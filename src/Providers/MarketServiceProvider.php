<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class MarketServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->microMarketHttp();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/markets.php', 'markets');
        $this->publishes([
            __DIR__.'/../../config/markets.php' => config_path('markets.php'),
        ], 'laravel-market-config');

        $this->registerCommands([
            __DIR__.'/../Commands/*',
        ]);
    }

    public function registerCommands($paths)
    {
        $allCommand = [];

        foreach ($paths as $path) {
            $commandPaths = glob($path);

            foreach ($commandPaths as $command) {
                $commandPath = realpath($command);
                if (! is_file($commandPath)) {
                    continue;
                }

                $commandClass = 'Fresns\\MarketManager\\Commands\\'.pathinfo($commandPath, PATHINFO_FILENAME);

                if (class_exists($commandClass)) {
                    $allCommand[] = $commandClass;
                }
            }
        }

        $this->commands($allCommand);
    }

    public function microMarketHttp()
    {
        if (Http::hasMacro('market')) {
            return;
        }

        Http::macro('market', function () {
            return Http::baseUrl(config('app.url'));
        });
    }
}
