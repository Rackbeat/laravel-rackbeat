<?php

namespace Rackbeat\Models;


use Rackbeat\Utils\Model;

class PurchaseOrder extends Model
{
	protected $entity     = 'purchase-orders';
	protected $primaryKey = 'number';
	protected $modelClass = self::class;

	public function getPDF() {
		return $this->request->handleWithExceptions( function () {
			$response = $this->request->client->get( "{$this->entity}/{$this->url_friendly_id}.pdf" );


			return json_decode( (string) $response->getBody() );
		} );
	}

    public function reopen()
    {

        return $this->request->handleWithExceptions( function () {

            $response = $this->request->client->post("{$this->entity}/{$this->url_friendly_id}/reopen");


            return json_decode((string)$response->getBody());
        } );
    }

    public function convertToInvoice($book = true)
    {

        return $this->request->handleWithExceptions( function () use ($book){

            $data = [
                'book'    => $book,
            ];

            $response = $this->request->client->post("{$this->entity}/{$this->url_friendly_id}/convert-to-invoice", [
                'json' => $data,
            ] );
            
            return json_decode((string)$response->getBody());
        } );
    }
}
