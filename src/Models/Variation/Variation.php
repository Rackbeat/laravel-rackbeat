<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 1.4.18.
 * Time: 00.02
 */

namespace Rackbeat\Models\Variation;


use Rackbeat\Builders\Variation\TypeBuilder;
use Rackbeat\Utils\Model;

class Variation extends Model
{
    public    $number;
    protected $entity     = 'variations';
    protected $primaryKey = 'number';

    public function types()
    {
        $types      = new TypeBuilder( $this->request );
        $old_entity = $types->getEntity();
        $types->setEntity(str_replace(':variation_number', $this->url_friendly_id, $old_entity));

        return $types;
    }
}