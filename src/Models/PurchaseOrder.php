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
			$response = $this->request->getClient()->get( "{$this->entity}/{$this->url_friendly_id}.pdf" )->throw();


			return $response->body();
		} );
	}

    public function reopen()
    {

        return $this->request->handleWithExceptions( function () {

            $response = $this->request->getClient()->post("{$this->entity}/{$this->url_friendly_id}/reopen")->throw();


            return $response->object();
        } );
    }

    public function convertToInvoice($book = true)
    {
        return $this->request->handleWithExceptions( function () use ($book){
            $response = $this->request->getClient()->asJson()->post("{$this->entity}/{$this->url_friendly_id}/convert-to-invoice", [
	            'book'    => $book,
            ])->throw();
            
            return $response->object();
        } );
    }
}
