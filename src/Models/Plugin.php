<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plugin extends Model
{
    const CACHE_DETAIL_PREFIX = 'plugin_id:';

    const CACHE_DETAIL_UNIKEY_PREFIX = 'plugin_unikey:';

    use HasFactory;
    use SoftDeletes;
    use Traits\PluginServiceTrait;

    protected $guarded = [];

    protected $casts = [
        'scene' => 'array',
    ];

    public function getAccessUrlAttribute()
    {
        if (!$this->attributes['access_path']) {
            return null;
        }

        if (filter_var($this->attributes['access_path'], FILTER_VALIDATE_URL)) {
            return trim($this->attributes['access_path'], '/');
        }
        
        $host = $this->plugin_host;
        if (!$host) {
            $host = config('app.url');
        }

        return trim($host, '/') . '/' . trim($this->attributes['access_path'], '/');
    }

    public function getSettingsUrlAttribute()
    {
        if (!$this->attributes['settings_path']) {
            return null;
        }

        if (filter_var($this->attributes['settings_path'], FILTER_VALIDATE_URL)) {
            return trim($this->attributes['settings_path'], '/');
        }

        $host = $this->plugin_host;
        if (!$host) {
            $host = config('app.url');
        }

        return trim($host, '/') . '/' . trim($this->attributes['settings_path'], '/');
    }
}
