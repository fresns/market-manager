<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
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
            // get plugin.json insert into database
            \Fresns\MarketManager\Listeners\PluginInstallingListener::class,
        ],

        'plugin:installed' => [
            //
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
            // delete database data
            \Fresns\MarketManager\Listeners\PluginUninstallingListener::class,
        ],

        'plugin:uninstalled' => [
            //
        ],

        // theme
        'theme:installing' => [
            // get theme.json insert into database
            \Fresns\MarketManager\Listeners\ThemeInstallingListener::class,
        ],

        'theme:uninstalling' => [
            // delete database data
            \Fresns\MarketManager\Listeners\ThemeUninstallingListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
