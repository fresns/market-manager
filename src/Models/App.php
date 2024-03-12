<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class App extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Traits\AppServiceTrait;

    const TYPE_PLUGIN = 1;
    const TYPE_THEME = 2;
    const TYPE_APP_REMOTE = 3;
    const TYPE_APP_DOWNLOAD = 4;

    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'panel_usages' => 'array',
    ];

    public function getPanelUsagesAttribute($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return $value ?? [];
    }

    public function scopeType($query, $value)
    {
        return $query->where('type', $value);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
