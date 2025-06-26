<?php

namespace Artisan\Routing\Exceptions;

class MethodNotAllowedException extends HttpException
{
    public function __construct(string $message = '', int $code=405)
    {
        parent::__construct($message, $code);
    }

}