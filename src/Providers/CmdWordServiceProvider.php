<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Providers;

use Illuminate\Support\ServiceProvider;
use Fresns\MarketManager\Services\MarketManagerService;

class CmdWordServiceProvider extends ServiceProvider implements \Fresns\CmdWordManager\Contracts\CmdWordProviderContract
{
    use \Fresns\CmdWordManager\Traits\CmdWordProviderTrait;

    protected $fsKeyName = 'MarketManager';

    /* This is a map of command word and its provider. */
    protected $cmdWordsMap = [
        ['word' => 'appDownload', 'provider' => [MarketManagerService::class, 'appDownload']],
        ['word' => 'appUpgrade', 'provider' => [MarketManagerService::class, 'appUpgrade']],
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerCmdWordProvider();
    }
}
