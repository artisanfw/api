<?php

namespace Artisan\Routing\Exceptions;

class ForbiddenException extends HttpException
{
    public function __construct(string $message = '', int $code=403)
    {
        parent::__construct($message, $code);
    }

}