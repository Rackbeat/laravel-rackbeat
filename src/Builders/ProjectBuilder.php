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
			$response =$this->request->client->post("{$this->entity}/upsert", [
				'json' => $data
			]);
			$responseData = $this->getResponse($response);

			return $this->populateModelsFromResponse($responseData->first());
		});
	}
}
