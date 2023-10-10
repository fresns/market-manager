<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Illuminate\Console\Command;

class MarketDeactivateCommand extends Command
{
    protected $signature = 'market:deactivate {fskey}';

    protected $description = 'deactivate fresns plugin';

    public function handle()
    {
        $fskey = $this->argument('fskey');

        $exitCode = $this->call('plugin:deactivate', [
            'fskey' => $fskey,
        ]);

        if ($exitCode == 0) {
            $this->info("Deactivate plugin successfully: $fskey");
        }

        return $exitCode;
    }
}
