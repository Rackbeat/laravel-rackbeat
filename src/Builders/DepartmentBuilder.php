<?php

namespace Rackbeat\Builders;

use Rackbeat\Models\Department;

class DepartmentBuilder extends Builder
{
	protected $entity = 'departments';
	protected $model = Department::class;

}
