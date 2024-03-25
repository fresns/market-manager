<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Fresns\MarketManager\Models\App;
use Illuminate\Support\Facades\Http;

class MarketUpgradeCommand extends MarketRequireCommand
{
    protected $signature = 'market:upgrade {fskey}
        {--install_type= : Plugin installation type}';

    protected $description = 'upgrade fresns plugin';

    public function handle()
    {
        return parent::handle();
    }

    public function getApp()
    {
        $fskey = $this->argument('fskey');

        $app = App::withTrashed()->where('fskey', $fskey)->first();
        if (! $app) {
            throw new \RuntimeException("{$fskey}: No plugin related information found");
        }

        return $app;
    }

    public function getDownloadUrlFromMarket()
    {
        $app = $this->getApp();

        // request market api
        $appResponse = Http::market()->get('/api/open-source/v3/upgrade', [
            'fskey' => $app->fskey,
            'version' => $app->version,
            'upgradeCode' => $app->upgrade_code,
        ]);

        if ($appResponse->failed()) {
            $this->error('Error: request failed (host or api)');

            return;
        }

        if ($appResponse->json('code') !== 0) {
            $this->error($appResponse->json('message'));

            return;
        }

        return $appResponse;
    }
}
