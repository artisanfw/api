<?php

namespace Artisan\Routing\Exceptions;

class UnsupportedMediaTypeException extends HttpException
{
    public function __construct(string $message = '', int $code=415)
    {
        parent::__construct($message, $code);
    }
}