<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Listeners;

use Fresns\MarketManager\Models\Plugin;

class PluginActivatedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     */
    public function handle($event): void
    {
        $fskey = $event['fskey'] ?? null;
        if (! $fskey) {
            return;
        }

        $plugin = Plugin::withTrashed()->where('fskey', $fskey)->first();
        if (! $plugin) {
            return;
        }

        $plugin->update([
            'is_enabled' => true,
        ]);
    }
}
