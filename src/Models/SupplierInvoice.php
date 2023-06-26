<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 31.3.18.
 * Time: 16.48
 */

namespace Rackbeat\Models;


use Rackbeat\Utils\Model;

class SupplierInvoice extends Model
{

	protected $entity     = 'supplier-invoices';
	protected $primaryKey = 'number';
	protected $modelClass = self::class;

	/**
	 * Book supplier invoice
	 *
	 * @param bool $mark_as_received
	 * @param bool $use_invoice_date
	 *
	 * @return mixed
	 * @throws \Rackbeat\Exceptions\RackbeatClientException
	 * @throws \Rackbeat\Exceptions\RackbeatRequestException
	 */
	public function book( $mark_as_received = false, $use_invoice_date = false ) {
		return $this->request->handleWithExceptions( function () use ( $mark_as_received, $use_invoice_date ) {
			$response = $this->request->getClient()->asJson()->post( "{$this->entity}/{$this->url_friendly_id}/book", [
				'mark_received'    => $mark_as_received,
				'use_invoice_date' => $use_invoice_date
			] );


			return $response->object();
		});
    }

    public function getPDF()
    {
        return $this->request->handleWithExceptions(function () {
            $response = $this->request->getClient()->get("{$this->entity}/{$this->url_friendly_id}.pdf")->throw();

	        return $response->body();
        });
    }
}