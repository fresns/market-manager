<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Models\Traits;

use Fresns\MarketManager\Models\App;

trait AppServiceTrait
{
    public static function handleAppData(array $data)
    {
        $dataType = $data['type'] ?? null;

        $appType = match ($dataType) {
            'plugin' => App::TYPE_PLUGIN,
            'theme' => App::TYPE_THEME,
            'app' => App::TYPE_APP_DOWNLOAD,
            default => App::TYPE_APP_REMOTE,
        };

        $app = App::withTrashed()->updateOrCreate([
            'fskey' => $data['fskey'],
        ], [
            'type' => $appType,
            'name' => $data['name'],
            'description' => $data['description'],
            'version' => $data['version'],
            'author' => $data['author'],
            'author_link' => $data['authorLink'] ?? null,
            'panel_usages' => $data['panelUsages'] ?? null,
            'access_path' => $data['accessPath'] ?? null,
            'settings_path' => $data['settingsPath'] ?? null,
            'is_standalone' => $data['isStandalone'] ?? false,
            'is_upgrade' => false,
            'upgrade_code' => $data['upgradeCode'] ?? null,
            'deleted_at' => null,
        ]);

        return $app;
    }

    public static function updateUpgradeCode(array $data)
    {
        $app = App::withTrashed()->where('fskey', $data['fskey'])->first();
        if ($app) {
            $app->update([
                'is_upgrade' => false,
                'upgrade_code' => $data['upgradeCode'] ?? null,
                'upgrade_version' => null,
                'deleted_at' => null,
            ]);
        }

        return $app;
    }
}
