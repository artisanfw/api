<?php

namespace Artisan\Routing\Exceptions;

class IAmATeapotException extends HttpException
{
    public function __construct(string $message = '', int $code=418)
    {
        parent::__construct($message, $code);
    }
}