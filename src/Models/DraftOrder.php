<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 19.4.18.
 * Time: 01.30
 */

namespace Rackbeat\Models;


use Rackbeat\Utils\Model;

class DraftOrder extends Model
{
    protected $entity     = 'orders/drafts';
    protected $primaryKey = 'number';

    public function getPDF()
    {
        return $this->request->handleWithExceptions( function () {
            return $this->request->client->get("{$this->entity}/{$this->url_friendly_id}.pdf")->getBody()
                ->getContents();
        } );
    }
}
