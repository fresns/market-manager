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
    protected $signature = 'market:upgrade {unikey} {type?}';

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

        return Http::market()->get('/api/open-source/v2/download', [
            'unikey' => $plugin->unikey,
            'version' => $plugin->version,
            'upgradeCode' => $plugin->upgrade_code,
        ]);
    }
}
