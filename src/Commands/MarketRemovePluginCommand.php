<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Illuminate\Support\Facades\Http;
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
        $this->call('plugin:uninstall', [
            'name' => $unikey,
            '--cleardata' => $this->option('cleardata') ?? null,
        ]);

        // delete plugin data(database)
        try {
            $plugin = $this->getPlugin();
            $plugin->forceDelete();
        } catch (\Throwable $e) {
            \info("Failed to delete plugin data: $unikey " . $e->getMessage());
        }

        $this->info("Delete plugin data successfully: $unikey");
    }

    public function getPlugin()
    {
        $unikey = $this->argument('unikey');

        $plugin = Plugin::findByUnikey($unikey);
        if (!$plugin) {
            throw new \RuntimeException("{$unikey}: No plugin related information found");
        }

        return $plugin;
    }
}
