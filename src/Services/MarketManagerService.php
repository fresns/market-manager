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

        $pluginResponse = Http::market()->get('/api/open-source/v2/download', [
            'fskey' => $wordBody['fskey'],
            'type' => 'download',
        ]);

        if (! $pluginResponse) {
            return $this->failure('Fresns Marketplace request failed, no response message received.');
        }

        $data = $pluginResponse->json('data');
        $extension = pathinfo(parse_url($data['zipBall'])['path'] ?? '', PATHINFO_EXTENSION);
        $data['extension'] = $extension;

        $result = collect($data)->only([
            'fskey',
            'version',
            'name',
            'description',
            'author',
            'zipBall',
            'upgradeCode',
            'extension',
        ]);

        event('app:download', [$result]);

        return $this->success($result);
    }

    public function appUpgrade(array $wordBody)
    {
        if (empty($wordBody['fskey'])) {
            return $this->failure('fskey cannot be empty');
        }

        $plugin = Plugin::findByFskey($wordBody['fskey']);
        if (! $$plugin) {
            return $this->failure("{$wordBody['fskey']} Application does not exist");
        }

        $pluginResponse = Http::market()->get('/api/open-source/v2/upgrade', [
            'fskey' => $wordBody['fskey'],
            'version' => $plugin['version'],
            'upgradeCode' => $plugin['upgrade_code'],
            'type' => 'download',
        ]);

        if (! $pluginResponse) {
            return $this->failure('Fresns Marketplace request failed, no response message received.');
        }

        $data = $pluginResponse->json('data');
        $extension = pathinfo(parse_url($data['zipBall'])['path'] ?? '', PATHINFO_EXTENSION);
        $data['extension'] = $extension;

        $result = collect($data)->only([
            'fskey',
            'version',
            'zipBall',
            'upgradeCode',
            'extension',
        ]);

        event('app:upgrade', [$result]);

        return $this->success($result);
    }
}
