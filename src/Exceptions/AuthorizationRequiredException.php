<?php

namespace Artisan\Routing\Exceptions;

class AuthorizationRequiredException extends HttpException
{
    public function __construct(string $message = '', int $code=401)
    {
        parent::__construct($message, $code);
    }

}