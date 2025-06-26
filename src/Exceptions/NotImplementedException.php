<?php

namespace Artisan\Routing\Exceptions;

class NotImplementedException extends HttpException
{
    public function __construct(string $message = '', int $code=501)
    {
        parent::__construct($message, $code);
    }
}