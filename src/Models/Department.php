<?php

namespace Rackbeat\Models;

use Rackbeat\Utils\Model;

class Department extends Model
{
	public $number;
	public $name;
	public $is_barred;
	protected $entity = 'departments';
	protected $primaryKey = 'number';
	protected $modelClass = self::class;
}
