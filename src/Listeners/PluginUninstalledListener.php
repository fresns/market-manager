<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Listeners;

use Fresns\MarketManager\Models\Plugin;

class PluginUninstalledListener
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
        if (! $unikey) {
            return;
        }

        $plugin = Plugin::findByUnikey($unikey);
        if (! $plugin) {
            return;
        }

        Plugin::deletePlugin($plugin->id);
    }
}
