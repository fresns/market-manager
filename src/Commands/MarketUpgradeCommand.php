<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Fresns\MarketManager\Models\Plugin;
use Illuminate\Support\Facades\Http;

class MarketUpgradeCommand extends MarketRequireCommand
{
    protected $signature = 'market:upgrade {fskey}
        {--install_type= : Plugin installation type}';

    protected $description = 'upgrade fresns extensions';

    public function handle()
    {
        return parent::handle();
    }

    public function getPlugin()
    {
        $fskey = $this->argument('fskey');

        $plugin = Plugin::withTrashed()->where('fskey', $fskey)->first();
        if (! $plugin) {
            throw new \RuntimeException("{$fskey}: No plugin related information found");
        }

        return $plugin;
    }

    public function getDownloadUrlFromMarket()
    {
        $plugin = $this->getPlugin();

        // request market api
        $pluginResponse = Http::market()->get('/api/open-source/v2/upgrade', [
            'fskey' => $plugin->fskey,
            'version' => $plugin->version,
            'upgradeCode' => $plugin->upgrade_code,
        ]);

        if ($pluginResponse->failed()) {
            $this->error('Error: request failed (host or api)');

            return;
        }

        if ($pluginResponse->json('code') !== 0) {
            $this->error($pluginResponse->json('message'));

            return;
        }

        return $pluginResponse;
    }
}
