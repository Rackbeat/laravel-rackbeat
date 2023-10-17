<?php

namespace Rackbeat\Builders;

use Rackbeat\Models\User;

class UserBuilder extends Builder
{
    protected $entity = 'users';
    protected $model  = User::class;
}