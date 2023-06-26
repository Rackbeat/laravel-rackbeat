<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 1.4.18.
 * Time: 00.02
 */

namespace Rackbeat\Models;


use Illuminate\Support\Arr;
use Rackbeat\Utils\Model;

class Product extends Model
{
	public    $number;
	protected $entity     = 'products';
	protected $primaryKey = 'number';
	protected $modelClass = self::class;

	public function inventoryMatrix( $location_id = null, array $filter = null ) {
		return $this->request->handleWithExceptions( function () use ( $location_id, $filter ) {

			$query = '';

			// We need to use location filter if user has provided any
			if ( ! is_null( $location_id ) ) {

				$query .= '?location_id=' . $location_id;
			}

			if ( !is_null( $filter ) ) {

				foreach ( $filter as $parameter => $value ) {

					if ( $query === '' ) {

						$query .= '?' . $parameter . '=' . $value;

					} else {

						$query .= '&' . $parameter . '=' . $value;
					}
				}
			}

			$response = $this->request->getClient()->get( "{$this->entity}/{$this->url_friendly_id}/variation-matrix{$query}")->throw();


			$isHtml   = filter_var( Arr::get( $filter, 'html', false ), FILTER_VALIDATE_BOOLEAN );

			return $isHtml ? $response->body() : $response->object();
		} );

	}

	public function variations( $variation_id = 1001 ) {
		return $this->request->handleWithExceptions( function () use ( $variation_id ) {
			$response = $this->request->getClient()->get("variations/{$this->url_friendly_id}/variation-matrix")->throw();

			return $response->body();
		} );
	}

	public function location($number = null) {
		return $this->request->handleWithExceptions(function () use ($number) {

			$response = $this->request->getClient()->get("{$this->entity}/{$this->url_friendly_id}/locations" . (($number !== null) ? '/' . $number : ''))->throw();


			$response = $response->object();

			if (isset($response->product_locations)) {
				return collect($response->product_locations);
			}

			return $response->product_location ?? $response;
		} );
	}

	/**
	 * Show reporting ledger for desired product https://app.rackbeat.com/reporting/ledger/{product_number}
	 * API docs: https://rackbeat.docs.apiary.io/#reference/inventory-reports/show
	 */
	public function ledger() {
		return $this->request->handleWithExceptions( function () {
			$response = $this->request->getClient()->get("reports/ledger/{$this->{ $this->primaryKey } }")->throw();

			return collect($response->object()->ledger_items);
		} );
	}

	public function fields() {

		return $this->request->handleWithExceptions( function () {
			$response = $this->request->getClient()->get("{$this->entity}/{$this->url_friendly_id}/fields")->throw();

			return collect($response->object()->field_values);
		} );
	}
}