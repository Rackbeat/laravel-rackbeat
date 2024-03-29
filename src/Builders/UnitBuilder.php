<?php

namespace Rackbeat\Builders;

use Rackbeat\Models\Unit;

class UnitBuilder extends Builder
{
    protected $entity = 'units';
    protected $model  = Unit::class;
}