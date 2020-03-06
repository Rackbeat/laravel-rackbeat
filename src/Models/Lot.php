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

    public function variations( $variation_id = 1001 )
    {
        return $this->request->handleWithExceptions( function () use ( $variation_id ) {

            $response = $this->request->client->get("variations/{$this->url_friendly_id}/variation-matrix");

            return (string)$response->getBody();
        } );
    }

    public function location()
    {
        return $this->request->handleWithExceptions( function () {

            $response = $this->request->client->get("{$this->entity}/{$this->url_friendly_id}/locations");

            return collect( json_decode( (string) $response->getBody() )->lot_locations );

        } );
    }

    /**
     * Show reporting ledger for desired product https://app.rackbeat.com/reporting/ledger/{product_number}
     * API docs: https://rackbeat.docs.apiary.io/#reference/inventory-reports/show
     */
    public function ledger()
    {
        return $this->request->handleWithExceptions( function () {

            $response =
                $this->request->client->get( "reports/ledger/{$this->{ $this->primaryKey } }" );

            return collect( json_decode( (string) $response->getBody() )->ledger_items );

        } );
    }
}