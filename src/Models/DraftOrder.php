<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 19.4.18.
 * Time: 01.30
 */

namespace Rackbeat\Models;


use Rackbeat\Builders\OrderNoteBuilder;
use Rackbeat\Builders\OrderShipmentBuilder;
use Rackbeat\Utils\Model;

class DraftOrder extends Model
{
	protected $entity     = 'orders/drafts';
	protected $primaryKey = 'number';
	protected $modelClass = self::class;

	public function getPDF() {
		return $this->request->handleWithExceptions( function () {
			$response = $this->request->getClient()->get( "{$this->entity}/{$this->url_friendly_id}.pdf" )->throw();

			return $response->body();
		} );
	}

	public function shipments() {
		$builder = new OrderShipmentBuilder( $this->request );

		return $builder->get( [
			[ 'order_number', '=', $this->url_friendly_id ],
		] );
	}

	public function notes() {
		$builder = new OrderNoteBuilder( $this->request );
		$builder->setEntity( str_replace( ':number', $this->url_friendly_id, $builder->getEntity() ) );

		return $builder->get();
	}
}
