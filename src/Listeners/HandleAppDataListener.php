<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Listeners;

use Fresns\MarketManager\Models\Plugin as PluginModel;

class HandleAppDataListener
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
        $event['isStandalone'] = true;

        $plugin = PluginModel::handleAppData($event);
    }
}
