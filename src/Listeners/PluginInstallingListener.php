<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Listeners;

use Fresns\PluginManager\Plugin;
use Fresns\MarketManager\Support\Json;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Fresns\MarketManager\Models\Plugin as PluginModel;

class PluginInstallingListener
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
        $unikey = $event['unikey'] ?? null;
        if (!$unikey) {
            return;
        }

        if (!class_exists(Plugin::class)) {
            return;
        }

        $plugin = new Plugin($unikey);
        $pluginJson = Json::make($plugin->getPluginJsonPath())->get();
        if (!$pluginJson) {
            \info('Failed to write plugin information to database');
            return;
        }

        $plugin = PluginModel::addPlugin($pluginJson);
    }
}
