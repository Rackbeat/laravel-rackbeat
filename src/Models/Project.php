<?php

namespace Rackbeat\Models;

use Rackbeat\Client\Utils\Model;

class Project extends Model
{
	public $number;
	public $name;
	public $description;
	protected $entity = 'projects';
	protected $primaryKey = 'number';
	protected $modelClass = self::class;
}
