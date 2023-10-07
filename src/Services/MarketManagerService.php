<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Services;

use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Fresns\MarketManager\Models\Plugin;
use Illuminate\Support\Facades\Http;

class MarketManagerService
{
    use CmdWordResponseTrait;

    public function appDownload(array $wordBody)
    {
        if (empty($wordBody['fskey'])) {
            return $this->failure('fskey cannot be empty');
        }

        $plugin = Plugin::withTrashed()->where('fskey', $wordBody['fskey'])->first();
        if ($plugin) {
            $pluginResponse = Http::market()->get('/api/open-source/v2/upgrade', [
                'fskey' => $plugin->fskey,
                'version' => $plugin->version,
                'upgradeCode' => $plugin->upgrade_code,
                'type' => 'download',
            ]);
        } else {
            $pluginResponse = Http::market()->get('/api/open-source/v2/download', [
                'fskey' => $wordBody['fskey'],
                'type' => 'download',
            ]);
        }

        if (! $pluginResponse) {
            return $this->failure('Fresns Marketplace request failed, no response message received.');
        }

        if ($pluginResponse->json('code') == 0) {
            $data = $pluginResponse->json('data');

            $result = collect($data)->only([
                'fskey',
                'version',
                'name',
                'description',
                'author',
                'authorLink',
                'zipBall',
                'upgradeCode',
            ])->all();

            event('app:handleData', [$result]);
        }

        return $this->success($pluginResponse->json('data'), $pluginResponse->json('message'), $pluginResponse->json('code'));
    }
}
