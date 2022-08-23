<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Models\Traits;

use Carbon\Carbon;
use Fresns\MarketManager\Models\Plugin;
use Illuminate\Support\Facades\Cache;

/**
 * Plugin.
 */
trait PluginServiceTrait
{
    public static function findById(?int $pluginId): ?Plugin
    {
        $cacheKey = Plugin::CACHE_DETAIL_PREFIX.$pluginId;

        return static::remember($cacheKey, function () use ($pluginId) {
            if (! $pluginId) {
                return null;
            }

            return Plugin::find($pluginId);
        });
    }

    public static function findByUnikey(?string $unikey)
    {
        $cacheKey = Plugin::CACHE_DETAIL_UNIKEY_PREFIX.$unikey;

        return static::remember($cacheKey, function () use ($unikey) {
            if (! $unikey) {
                return null;
            }

            return Plugin::where('unikey', $unikey)->first();
        });
    }

    public static function addPlugin(array $data)
    {
        $plugin = Plugin::updateOrCreate([
            'unikey' => $data['unikey'],
        ], [
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'],
            'version' => $data['version'],
            'author' => $data['author'],
            'author_link' => $data['authorLink'] ?? null,
            'scene' => $data['scene'] ?? null,
            'plugin_host' => $data['pluginHost'] ?? null,
            'access_path' => $data['accessPath'] ?? null,
            'settings_path' => $data['settingsPath'] ?? null,
            'theme_functions' => $data['functions'] ?? false,
            'is_upgrade' => $data['is_upgrade'] ?? 0,
            'upgrade_code' => $data['upgradeCode'] ?? null,
            'upgrade_version' => $data['upgradeVersion'] ?? null,
            'is_enable' => $data['isEnable'] ?? 0,
        ]);

        static::forgetCache($plugin->id);

        return $plugin;
    }

    public static function upgrade(array $data)
    {
        $plugin = Plugin::findByUnikey($data['unikey']);
        if (! $plugin) {
            throw new \RuntimeException("Plugin not found {$data['unikey']}");
        }

        $plugin->update([
            'upgrade_code' => $data['upgrade_code'],
        ]);

        static::forgetCache($plugin->id);

        return $plugin;
    }

    public static function deletePlugin(int $pluginId)
    {
        $plugin = Plugin::findById($pluginId);
        if (! $plugin) {
            return false;
        }

        return $plugin->forceDelete();
    }

    public static function remember(string $cacheKey, callable|Carbon|null $cacheTime, callable $callable = null, $forever = false)
    {
        $nullCacheKey = 'null_key:'.$cacheKey;

        $nullKeyNum = 10;
        if (Cache::get($nullCacheKey) > $nullKeyNum) {
            return null;
        }

        // Use default cache time
        if (is_callable($cacheTime)) {
            $callable = $cacheTime;

            // Prevent cache avalanches by randomizing the cache time for different data.
            // From half an hour to 1 day
            $defaultCacheTime = [1800, 3600, 7200, 14400, 28800, 57600, 86400];

            $cacheSeconds = rand(0, 100) % count($defaultCacheTime);
            $cacheTime = now()->addSeconds($cacheSeconds);
        }

        if (! is_callable($callable)) {
            return null;
        }

        if ($forever) {
            $data = Cache::rememberForever($cacheKey, $callable);
        } else {
            $data = Cache::remember($cacheKey, $cacheTime, $callable);
        }

        if (! $data) {
            Cache::pull($cacheKey);

            $currentCacheKeyNullNum = (int) Cache::get($nullCacheKey);

            $nullKeyCacheTime = 60; // 60s
            Cache::put($nullCacheKey, ++$currentCacheKeyNullNum, now()->addSeconds($nullKeyCacheTime));
        }

        return $data;
    }

    public static function forgetCache(int $pluginId)
    {
        $plugin = Plugin::findById($pluginId);
        if (! $plugin) {
            return false;
        }

        Cache::forget(Plugin::CACHE_DETAIL_PREFIX.$plugin->id);
        Cache::forget(Plugin::CACHE_DETAIL_UNIKEY_PREFIX.$plugin->unikey);

        return true;
    }
}
