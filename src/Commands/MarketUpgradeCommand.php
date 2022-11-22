<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Fresns\MarketManager\Models\Plugin;
use Illuminate\Support\Facades\Http;

class MarketUpgradeCommand extends MarketRequireCommand
{
    protected $signature = 'market:upgrade {unikey} {package_type=plugin}
        {--install_type= : Plugin installation type}';

    protected $description = 'upgrade fresns extensions';

    public function handle()
    {
        return parent::handle();
    }

    public function getPlugin()
    {
        $unikey = $this->argument('unikey');

        $plugin = Plugin::findByUnikey($unikey);
        if (! $plugin) {
            throw new \RuntimeException("{$unikey}: No plugin related information found");
        }

        return $plugin;
    }

    public function getDownloadUrlFromMarket()
    {
        $plugin = $this->getPlugin();

        // request market api
        $pluginResponse = Http::market()->get('/api/open-source/v2/download', [
            'unikey' => $plugin->unikey,
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
