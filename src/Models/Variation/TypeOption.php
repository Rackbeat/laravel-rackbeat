<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 1.4.18.
 * Time: 00.02
 */

namespace Rackbeat\Models\Variation;


use Rackbeat\Utils\Model;
use Rackbeat\Utils\Request;

class TypeOption extends Model
{
	public    $id;
	protected $entity     = 'variations/:variation_number/types/:type_number/options';
	protected $primaryKey = 'id';
	protected $modelClass = self::class;

	public function __construct( Request $request, $data = [] ) {
		parent::__construct( $request, $data );

		$this->entity = str_replace( ':variation_number', $this->variation_id, $this->entity );
		$this->entity = str_replace( ':type_number', $this->type_id, $this->entity );
	}
}