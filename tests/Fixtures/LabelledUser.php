<?php

namespace Intrfce\FFFlags\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Intrfce\FFFlags\Contracts\HasFeatureSelectorLabel;

class LabelledUser extends Model implements HasFeatureSelectorLabel
{
    protected $table = 'users';

    protected $guarded = [];

    public function getFeatureSelectorLabel(): string
    {
        return $this->name;
    }
}
