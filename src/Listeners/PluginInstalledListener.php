<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Listeners;

use Fresns\MarketManager\Models\Plugin as PluginModel;
use Fresns\MarketManager\Support\Json;
use Fresns\PluginManager\Plugin;

class PluginInstalledListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $fskey = $event['fskey'] ?? null;
        if (! $fskey) {
            return;
        }

        if (! class_exists(Plugin::class)) {
            return;
        }

        $plugin = new Plugin($fskey);
        $pluginJson = Json::make($plugin->getPluginJsonPath())->get();
        if (! $pluginJson) {
            \info('Failed to write plugin information to database');

            return;
        }

        $plugin = PluginModel::addPlugin($pluginJson);
    }
}
