<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 1.4.18.
 * Time: 00.02
 */

namespace Rackbeat\Models;


use Rackbeat\Utils\Model;

class Lot extends Model
{
	public    $number;
	protected $entity     = 'lots';
	protected $primaryKey = 'number';
	protected $modelClass = self::class;

	public function variations( $variation_id = 1001 ) {
		return $this->request->handleWithExceptions( function () use ( $variation_id ) {

			$response = $this->request->getClient()->get( "variations/{$this->url_friendly_id}/variation-matrix" )->throw();

			return $response->object();
		} );
    }
	
    public function location($number = null) {
		return $this->request->handleWithExceptions(function () use ($number) {
			$response = $this->request->getClient()->get("{$this->entity}/{$this->url_friendly_id}/locations" . (($number !== null) ? '/' . $number : ''))->throw();

			$response = $response->object();

			if (isset($response->lot_locations)) {
				return collect($response->lot_locations);
			}

			return $response->lot_location ?? $response;
		} );
	}

    /**
     * Show reporting ledger for desired product https://app.rackbeat.com/reporting/ledger/{product_number}
     * API docs: https://rackbeat.docs.apiary.io/#reference/inventory-reports/show
     */
    public function ledger()
    {
        return $this->request->handleWithExceptions( function () {
            $response = $this->request->getClient()->get("reports/ledger/{$this->{ $this->primaryKey } }")->throw();

            return collect(data_get($response->object(), 'ledger_items'));

        } );
    }
}
