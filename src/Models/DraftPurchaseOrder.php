<?php

namespace Rackbeat\Models;


use Rackbeat\Utils\Model;

class DraftPurchaseOrder extends Model
{
	protected $entity     = 'purchase-orders/drafts';
	protected $primaryKey = 'number';
	protected $modelClass = self::class;

	public function getPDF() {
		return $this->request->handleWithExceptions( function () {
			$response = $this->request->getClient()->get( "{$this->entity}/{$this->url_friendly_id}.pdf" )->throw();

			return $response->body();
		} );
	}
}
