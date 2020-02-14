<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 31.3.18.
 * Time: 17.03
 */

namespace Rackbeat\Utils;


class Model
{
    protected $entity;
    protected $primaryKey;
    protected $url_friendly_id;
    protected $modelClass = self::class;
    protected $fillable   = [];

    /**
     * @var Request
     */
    protected $request;

    public function __construct( Request $request, $data = [] )
    {
        $this->request = $request;
        $data          = (array) $data;

        foreach ( $data as $key => $value ) {

            $customSetterMethod = 'set' . ucfirst(\Str::camel($key)) . 'Attribute';

            if ( !method_exists( $this, $customSetterMethod ) ) {

                $this->setAttribute( $key, $value );

            } else {

                $this->setAttribute( $key, $this->{$customSetterMethod}( $value ) );
            }
        }
    }

    protected function setAttribute( $attribute, $value )
    {
        if ($attribute === $this->primaryKey) {

            $this->url_friendly_id = rawurlencode(rawurlencode($value));
        }

        $this->{$attribute} = $value;

    }

    public function __toString()
    {
        return json_encode( $this->toArray() );
    }

    public function toArray()
    {
        $data       = [];
        $class      = new \ReflectionObject( $this );
        $properties = $class->getProperties( \ReflectionProperty::IS_PUBLIC );

        /** @var \ReflectionProperty $property */
        foreach ( $properties as $property ) {

            $data[ $property->getName() ] = $this->{$property->getName()};
        }

        return $data;
    }

    public function delete()
    {
        return $this->request->handleWithExceptions( function () {

            return $this->request->client->delete("{$this->entity}/{$this->url_friendly_id}");
        } );
    }

    public function update( $data = [] )
    {

        return $this->request->handleWithExceptions( function () use ( $data ) {

            $response = $this->request->client->put("{$this->entity}/{$this->url_friendly_id}", [
                'json' => $data,
            ]);

            $responseData = collect( json_decode( (string) $response->getBody() ) );

            return new $this->modelClass( $this->request, $responseData->first() );
        } );
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity( $new_entity )
    {
        $this->entity = $new_entity;
    }
}