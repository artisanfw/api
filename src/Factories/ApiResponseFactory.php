<?php

namespace Artisan\Routing\Factories;

use Artisan\Routing\Entities\ApiOptions;
use Artisan\Routing\Entities\ApiResponse;
use Artisan\Routing\Entities\JsonApiResponse;
use Artisan\Routing\Entities\XmlApiResponse;
use Artisan\Routing\Interfaces\IApiResponse;

class ApiResponseFactory
{
    public function getResponseInstance(string $responseType): IApiResponse
    {
        if (ApiOptions::RESPONSE_JSON === $responseType) {
            $response = new JsonApiResponse();
        } elseif (ApiOptions::RESPONSE_XML === $responseType) {
            $response = new XmlApiResponse();
        } else {
            $response = new ApiResponse();
        }

        return $response;
    }

}