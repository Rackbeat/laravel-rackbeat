<?php


namespace Rackbeat\Builders;

use Illuminate\Http\Client\Response;
use Rackbeat\Traits\ApiFiltering;
use Rackbeat\Utils\Model;
use Rackbeat\Utils\Request;


class Builder
{
	use ApiFiltering;

	protected $entity;
	/** @var Model */
	protected $model;
	protected $request;

	public function __construct( Request $request ) {
		$this->request = $request;
	}


	/**
	 * Get only first page of resources, you can also set query parameters, default limit is 1000
	 *
	 * @param array $filters
	 *
	 * @return mixed
	 * @throws \Rackbeat\Exceptions\RackbeatClientException
	 * @throws \Rackbeat\Exceptions\RackbeatRequestException
	 */
	public function get( $filters = [] ) {

		$urlFilters = $this->parseFilters( $filters );

		return $this->request->handleWithExceptions( function () use ( $urlFilters ) {
			$response     = $this->request->getClient()->get( "{$this->entity}{$urlFilters}" )->throw();
			$fetchedItems = $this->getResponse( $response );

			return $this->populateModelsFromResponse( $fetchedItems->first() );
		} );
	}


	/**
	 * @param $response
	 *
	 * @return \Illuminate\Support\Collection|Model
	 */
	protected function populateModelsFromResponse( $response ) {
		$items = collect();
		if ( is_iterable( $response ) ) {
			foreach ( $response as $index => $item ) {
				/** @var Model $model */
				$modelClass = $this->getModelClass( $item );
				$model      = new $modelClass( $this->request, $item );

				$items->push( $model );
			}
		} else {
			$modelClass = $this->getModelClass( $response );

			return new $modelClass( $this->request, $response );
		}


		return $items;

	}

	/**
	 * It will iterate over all pages until it does not receive empty response, you can also set query parameters,
	 * default limit per page is 1000
	 *
	 * @param array $filters
	 *
	 * @return mixed
	 */
	public function all( $filters = [] ) {
		$page = 1;
		$this->limit( 1000 );

		$items = collect();

		$response = function ( $filters, $page ) {
			$this->page( $page );

			$urlFilters = $this->parseFilters( $filters );

			return $this->request->handleWithExceptions( function () use ( $urlFilters ) {
				$response = $this->request->getClient()->get( "{$this->entity}{$urlFilters}" )->throw();

				$responseData = $response->object();
				$fetchedItems = $this->getResponse( $response );
				$pages        = $responseData->pages;
				$items        = $this->populateModelsFromResponse( $fetchedItems->first() );

				return (object) [
					'items' => $items,
					'pages' => $pages,
				];
			} );
		};

		do {

			$resp = $response( $filters, $page );

			$items = $items->merge( $resp->items );
			$page++;

		} while ( $page <= $resp->pages );


		return $items;
	}

	/**
	 * It will iterate over all pages until it does not receive empty response, you can also set query parameters,
	 * Return a Generator that you' handle first before quering the next offset
	 *
	 * @param int $chunkSize
	 *
	 * @return Generator
	 */
	public function allWithGenerator(int $chunkSize = 50)
	{
		$page = 1;
		$this->limit($chunkSize);

		$response = function ($page) {
			$this->page($page);
			$urlFilters = $this->parseFilters();

			return $this->request->handleWithExceptions(function () use ($urlFilters) {
				$response = $this->request->getClient()->get("{$this->entity}{$urlFilters}")->throw();

				$responseData = $response->object();
				$fetchedItems = $this->getResponse($response);
				$pages        = $responseData->pages;
				$items        = $this->populateModelsFromResponse($fetchedItems->first());

				return (object) [
					'items' => $items,
					'pages' => $pages,
				];
			});
		};

		do {
			$resp = $response($page);

			$countResults = count($resp->items);
			if ($countResults === 0) {
				break;
			}
			// make a generator of the results and return them
			// so the logic will handle them before the next iteration
			// in order to avoid memory leaks
			foreach ($resp->items as $result) {
				yield $result;
			}

			unset($resp);

			$page++;
		} while ($countResults === $chunkSize);
	}

	/**
	 * Find single resource by its id filed, it also accepts query parameters
	 *
	 * @param       $id
	 * @param array $filters
	 *
	 * @return mixed
	 * @throws \Rackbeat\Exceptions\RackbeatClientException
	 * @throws \Rackbeat\Exceptions\RackbeatRequestException
	 */
	public function find( $id, $filters = [] ) {
		unset( $this->wheres['limit'], $this->wheres['page'] );

		$urlFilters = $this->parseFilters( $filters );
		$id         = rawurlencode( rawurlencode( $id ) );

		return $this->request->handleWithExceptions( function () use ( $id, $urlFilters ) {
			$response     = $this->request->getClient()->get( "{$this->entity}/{$id}{$urlFilters}" )->throw();
			$responseData = $this->getResponse( $response );

			return $this->populateModelsFromResponse( $responseData->first() );
		} );
	}

	/**
	 * Create new resource and return created model
	 *
	 * @param $data
	 *
	 * @return mixed
	 * @throws \Rackbeat\Exceptions\RackbeatClientException
	 * @throws \Rackbeat\Exceptions\RackbeatRequestException
	 */
	public function create( $data ) {
		return $this->request->handleWithExceptions( function () use ( $data ) {
			$response     = $this->request->getClient()->asJson()->post( $this->entity, $data)->throw();
			$responseData = $this->getResponse( $response );

			return $this->populateModelsFromResponse( $responseData->first() );
		} );
	}

	/**
	 * Directly update an entity without finding it first.
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return mixed
	 * @throws \Rackbeat\Exceptions\RackbeatClientException
	 * @throws \Rackbeat\Exceptions\RackbeatRequestException
	 */
	public function update( $id, $data ) {
		return $this->request->handleWithExceptions( function () use ( $id, $data ) {
			$response = $this->request->getClient()->asJson()->put("{$this->entity}/{$id}", $data)->throw();

			$responseData = $this->getResponse( $response );

			return new $this->model($this->request, $responseData->first());
		} );
	}

	public function getEntity() {
		return $this->entity;
	}

	public function setEntity( $new_entity ) {
		$this->entity = $new_entity;

		return $this->entity;
	}

	protected function getModelClass( $item ) {
		return $this->model;
	}

	protected function getResponse( Response $response ) {
		return collect( $response->object() );
	}
}
