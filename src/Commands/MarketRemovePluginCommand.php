<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Fresns\MarketManager\Models\Plugin;
use Illuminate\Console\Command;

class MarketRemovePluginCommand extends Command
{
    protected $signature = 'market:remove-plugin {unikey}
        {--cleardata : Trigger clear plugin data}';

    protected $description = 'remove fresns extensions';

    public function handle()
    {
        $unikey = $this->argument('unikey');

        // uninstall plugin
        $exitCode = $this->call('plugin:uninstall', [
            'name' => $unikey,
            '--cleardata' => $this->option('cleardata') ?? null,
        ]);

        if ($exitCode == 0) {
            $this->info("Delete plugin data successfully: $unikey");
        }

        return $exitCode;
    }
}
