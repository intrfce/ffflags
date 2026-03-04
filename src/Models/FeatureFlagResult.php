<?php

namespace Intrfce\FFFlags\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlagResult extends Model
{
    protected $table = 'ffflags_feature_cache';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'result' => 'boolean',
        ];
    }
}
