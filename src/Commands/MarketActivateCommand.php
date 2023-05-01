<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Illuminate\Console\Command;

class MarketActivateCommand extends Command
{
    protected $signature = 'market:activate {fskey}';

    protected $description = 'activate fresns extensions';

    public function handle()
    {
        $fskey = $this->argument('fskey');

        $exitCode = $this->call('plugin:activate', [
            'fskey' => $fskey,
        ]);

        if ($exitCode == 0) {
            $this->info("Activate plugin successfully: $fskey");
        }

        return $exitCode;
    }
}
