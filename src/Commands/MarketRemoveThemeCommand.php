<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Fresns\MarketManager\Models\Plugin;

class MarketRemoveThemeCommand extends MarketRemovePluginCommand
{
    protected $signature = 'market:remove-theme {fskey}';

    protected $description = 'remove fresns themes';

    public function handle()
    {
        $fskey = $this->argument('fskey');

        // uninstall theme
        $exitCode = $this->call('theme:uninstall', [
            'fskey' => $fskey,
        ]);

        // delete theme data(database)
        try {
            $plugin = $this->getTheme();
            $plugin->delete();
        } catch (\Throwable $e) {
            \info("Failed to delete theme data: $fskey ".$e->getMessage());

            return;
        }

        $this->info("Delete theme data successfully: $fskey");
    }

    public function getTheme()
    {
        $fskey = $this->argument('fskey');

        $plugin = Plugin::withTrashed()->where('fskey', $fskey)->first();
        if (! $plugin) {
            throw new \RuntimeException("{$fskey}: No theme related information found");
        }

        return $plugin;
    }
}
