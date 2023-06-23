<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Models\Traits;

use Fresns\MarketManager\Models\Plugin;

trait PluginServiceTrait
{
    public static function handleAppData(array $data)
    {
        $plugin = Plugin::withTrashed()->updateOrCreate([
            'fskey' => $data['fskey'],
        ], [
            'name' => $data['name'],
            'type' => $data['type'] ?? Plugin::TYPE_THEME,
            'description' => $data['description'],
            'version' => $data['version'],
            'author' => $data['author'],
            'author_link' => $data['authorLink'] ?? null,
            'scene' => $data['scene'] ?? null,
            'access_path' => $data['accessPath'] ?? null,
            'settings_path' => $data['settingsPath'] ?? null,
            'theme_functions' => $data['functions'] ?? false,
            'deleted_at' => null,
        ]);

        return $plugin;
    }

    public static function updateUpgradeCode(array $data)
    {
        $plugin = Plugin::withTrashed()->where('fskey', $data['fskey'])->first();
        if ($plugin) {
            $plugin->update([
                'is_upgrade' => false,
                'upgrade_code' => $data['upgradeCode'] ?? null,
                'upgrade_version' => null,
                'deleted_at' => null,
            ]);
        }

        return $plugin;
    }
}
