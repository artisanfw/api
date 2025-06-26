<?php

namespace Artisan\Routing\Exceptions;

class ServiceTemporarilyUnavailableException extends HttpException
{
    public function __construct(string $message = '', int $code=503)
    {
        parent::__construct($message, $code);
    }
}