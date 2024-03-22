<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Listeners;

use Fresns\MarketManager\Models\App;
use Fresns\MarketManager\Support\Json;
use Fresns\ThemeManager\Theme;

class ThemeInstalledListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     */
    public function handle($event): void
    {
        $fskey = $event['fskey'] ?? null;
        if (! $fskey) {
            return;
        }

        if (! class_exists(Theme::class)) {
            return;
        }

        $theme = new Theme($fskey);
        $themeJson = Json::make($theme->getThemeJsonPath())->get();
        if (! $themeJson) {
            \info('Failed to write theme information to database');

            return;
        }

        if ($themeJson['functions'] ?? null) {
            $themeJson['settingsPath'] = $fskey;
        }

        App::handleAppData($themeJson);
    }
}
