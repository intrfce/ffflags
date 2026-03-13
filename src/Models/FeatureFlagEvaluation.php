<?php

namespace Intrfce\FFFlags\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlagEvaluation extends Model
{
    protected $table = 'ffflags_evaluations';

    protected $fillable = [
        'feature_slug',
        'scope_type',
        'scope_id',
        'result',
        'call_file',
        'call_line',
        'conditions_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'boolean',
            'conditions_snapshot' => 'array',
        ];
    }
}
