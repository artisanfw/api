<?php

namespace Artisan\Routing\Exceptions;

class BadRequestException extends HttpException
{
    public function __construct(string $message, int $code=400)
    {
        parent::__construct($message, $code);
    }

}