<?php

namespace Rackbeat\Builders;

use Rackbeat\Models\Project;

class ProjectBuilder extends Builder
{
	protected $entity = 'projects';
	protected $model = Project::class;

	public function upsert($data): Project
	{
		// todo
	}
}
