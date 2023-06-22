<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // plugin
        'plugin:installing' => [
            //
        ],

        'plugin:installed' => [
            // get plugin.json insert into database
            \Fresns\MarketManager\Listeners\PluginInstalledListener::class,
        ],

        'plugin:activating' => [
            //
        ],

        'plugin:activated' => [
            // activate plugin
            \Fresns\MarketManager\Listeners\PluginActivatedListener::class,
        ],

        'plugin:deactivating' => [
            //
        ],

        'plugin:deactivated' => [
            // deactivate plugin
            \Fresns\MarketManager\Listeners\PluginDeactivatedListener::class,
        ],

        'plugin:uninstalling' => [
            //
        ],

        'plugin:uninstalled' => [
            // delete database data
            \Fresns\MarketManager\Listeners\PluginUninstalledListener::class,
        ],

        // theme
        'theme:installing' => [
            //
        ],

        'theme:installed' => [
            // get theme.json insert into database
            \Fresns\MarketManager\Listeners\ThemeInstalledListener::class,
        ],

        'theme:uninstalling' => [
            //
        ],

        'theme:uninstalled' => [
            // delete database data
            \Fresns\MarketManager\Listeners\ThemeUninstalledListener::class,
        ],

        'app:download' => [
            // app download from market, insert app meta info into database
            \Fresns\MarketManager\Listeners\AppDownloadListener::class,
        ],

        'app:upgrade' => [
            // app download from market, insert app meta info into database
            \Fresns\MarketManager\Listeners\AppUpgradeListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
