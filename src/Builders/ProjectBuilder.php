<?php

namespace Rackbeat\Builders;

use Rackbeat\Models\Project;

class ProjectBuilder extends Builder
{
	protected $entity = 'projects';
	protected $model = Project::class;

	public function upsert($data): Project
	{
		return $this->request->handleWithExceptions(function () use ($data) {
			$response =$this->request->getClient()->asJson()->post("{$this->entity}/upsert", $data);

			$responseData = $this->getResponse($response);

			return $this->populateModelsFromResponse($responseData->first());
		});
	}
}
