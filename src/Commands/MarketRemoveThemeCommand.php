<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Fresns\MarketManager\Models\Plugin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MarketRemoveThemeCommand extends MarketRemovePluginCommand
{
    protected $signature = 'market:remove-theme {unikey}
        {--cleardata : Trigger clear theme data}';

    protected $description = 'remove fresns themes';

    public function handle()
    {
        $unikey = $this->argument('unikey');

        // uninstall theme
        $this->call('theme:uninstall', [
            'name' => $unikey,
            '--cleardata' => $this->option('cleardata') ?? null,
        ]);

        // delete theme data(database)
        try {
            $plugin = $this->getTheme();
            $plugin->forceDelete();
        } catch (\Throwable $e) {
            \info("Failed to delete theme data: $unikey ".$e->getMessage());
        }

        $this->info("Delete theme data successfully: $unikey");
    }

    public function getTheme()
    {
        $unikey = $this->argument('unikey');

        $plugin = Plugin::findByUnikey($unikey);
        if (! $plugin) {
            throw new \RuntimeException("{$unikey}: No theme related information found");
        }

        return $plugin;
    }
}
