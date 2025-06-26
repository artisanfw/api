<?php

namespace Api;

use Artisan\Routing\Entities\ApiOptions;
use Artisan\Routing\Managers\ApiManager;
use Artisan\Routing\Services\ApiService;
use Symfony\Component\HttpFoundation\Response;


class Bootstrap
{
    public function run(): void
    {
        define('PROJECT_DIR', __DIR__);

        try {
            $apiOptions = (new ApiOptions())
                ->setLabel('artisan_api_example')
                ->setRequestType(ApiOptions::REQUEST_JSON)
                ->setResponseType(ApiOptions::RESPONSE_JSON)
                ->setAllowedHeaders(ApiOptions::COMMON_CORS_HEADERS)
                ->setAllowedMethods(ApiOptions::COMMON_CORS_METHODS);

            ApiService::initialize($apiOptions);

            $apiManager = new ApiManager();
            $apiManager->addRouter(new HomeRouter());

            $apiManager->processRequest(ApiService::i()->getRequest());
        } catch (\Throwable $t) {
            error_log($t);
            $response = new Response(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->send();
        }
    }
}
