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
        $fskey = $event['fskey'] ?? null;
        if (! $fskey) {
            return;
        }

        $plugin = Plugin::findByFskey($fskey);
        if (! $plugin) {
            return;
        }

        Plugin::deletePlugin($plugin->id);
    }
}
