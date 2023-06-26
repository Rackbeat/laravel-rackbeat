<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 31.3.18.
 * Time: 15.37
 */

namespace Rackbeat\Builders;


use Rackbeat\Models\Webhook;

class WebhookBuilder extends Builder
{
    protected $entity = 'webhooks';
    protected $model  = Webhook::class;

    public function deleteWebhook(int $id)
    {
        return $this->request->handleWithExceptions( function () use ( $id ) {
            $this->request->getClient()->delete( "{$this->entity}/{$id}" )->throw();
        } );
    }
}