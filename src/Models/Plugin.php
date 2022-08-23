<?php

namespace Fresns\MarketManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
