<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Illuminate\Console\Command;

class MarketDeactivateCommand extends Command
{
    protected $signature = 'market:deactivate {unikey}';

    protected $description = 'deactivate fresns extensions';

    public function handle()
    {
        $unikey = $this->argument('unikey');

        $this->call('plugin:deactivate', [
            'name' => $unikey,
        ]);

        $this->info("Deactivate plugin successfully: $unikey");
    }
}
