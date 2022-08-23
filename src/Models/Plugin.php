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
}
