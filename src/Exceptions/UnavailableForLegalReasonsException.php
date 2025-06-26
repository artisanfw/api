<?php

namespace Artisan\Routing\Exceptions;

class UnavailableForLegalReasonsException extends HttpException
{
    public function __construct(string $message = '', int $code=451)
    {
        parent::__construct($message, $code);
    }
}