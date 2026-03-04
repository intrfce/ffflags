<?php

namespace Intrfce\FFFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Intrfce\FFFlags\Enums\ScopeCondition;

class FeatureFlagModelScope extends Model
{
    protected $table = 'ffflags_model_scopes';

    protected $fillable = [
        'feature_slug',
        'scope_type',
        'condition',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'condition' => ScopeCondition::class,
            'value' => 'array',
        ];
    }
}
