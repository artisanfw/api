<?php

namespace Artisan\Routing\Exceptions;

class PaymentRequiredException extends HttpException
{
    public function __construct(string $message = '', int $code=402)
    {
        parent::__construct($message, $code);
    }

}