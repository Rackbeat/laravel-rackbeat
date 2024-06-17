<?php

namespace Rackbeat\Models;

use Rackbeat\Utils\Model;

class Unit extends Model
{

    protected $entity     = 'units';
    protected $primaryKey = 'number';
    protected $modelClass = self::class;
}