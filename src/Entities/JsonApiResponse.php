<?php

namespace Artisan\Routing\Entities;

use Artisan\Routing\Exceptions\InternalServerErrorException;

class JsonApiResponse extends ApiResponse
{
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * @throws InternalServerErrorException
     */
    public function beforeSend(): void
    {
        if (is_array($this->getPayload())) {
            $payload = json_encode($this->getPayload());
            if ($payload === false) {
                throw new InternalServerErrorException();
            }
        } else $payload = (string) $this->getPayload();

        $this->setPayload($payload);
    }

}