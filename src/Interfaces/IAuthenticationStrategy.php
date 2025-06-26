<?php

namespace Artisan\Routing\Interfaces;

use Artisan\Routing\Exceptions\AuthorizationRequiredException;

interface IAuthenticationStrategy
{
    /**
     * @throws AuthorizationRequiredException
     */
    public function authenticate(): void;
}