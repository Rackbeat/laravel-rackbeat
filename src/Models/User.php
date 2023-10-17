<?php

namespace Rackbeat\Models;

use Rackbeat\Utils\Model;

class User extends Model
{

	protected $entity     = 'users';
	protected $primaryKey = 'id';
	protected $modelClass = self::class;
}