<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Illuminate\Console\Command;

class MarketActivateCommand extends Command
{
    protected $signature = 'market:activate {unikey}';

    protected $description = 'activate fresns extensions';

    public function handle()
    {
        $unikey = $this->argument('unikey');

        $this->call('plugin:activate', [
            'name' => $unikey,
        ]);

        $this->info("Activate plugin successfully: $unikey");
    }
}
