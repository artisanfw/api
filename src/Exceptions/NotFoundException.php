<?php

namespace Artisan\Routing\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct(string $message = '', int $code=404)
    {
        parent::__construct($message, $code);
    }
}