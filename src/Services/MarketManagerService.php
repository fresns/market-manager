<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Services;

use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Fresns\MarketManager\Models\App;
use Illuminate\Support\Facades\Http;

class MarketManagerService
{
    use CmdWordResponseTrait;

    public function appDownload(array $wordBody)
    {
        if (empty($wordBody['fskey'])) {
            return $this->failure('fskey cannot be empty');
        }

        $app = App::withTrashed()->where('fskey', $wordBody['fskey'])->first();
        if ($app) {
            $appResponse = Http::market()->get('/api/open-source/v3/upgrade', [
                'fskey' => $app->fskey,
                'version' => $app->version,
                'upgradeCode' => $app->upgrade_code,
                'type' => 'download',
            ]);
        } else {
            $appResponse = Http::market()->get('/api/open-source/v3/download', [
                'fskey' => $wordBody['fskey'],
                'type' => 'download',
            ]);
        }

        if (! $appResponse) {
            return $this->failure('Fresns Marketplace request failed, no response message received.');
        }

        if ($appResponse->json('code') == 0) {
            $data = $appResponse->json('data');

            $result = collect($data)->only([
                'fskey',
                'type',
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

        return $this->success($appResponse->json('data'), $appResponse->json('message'), $appResponse->json('code'));
    }
}
