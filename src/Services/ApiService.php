<?php

namespace Artisan\Routing\Services;

use Artisan\Routing\Entities\ApiOptions;
use Artisan\Routing\Factories\ApiResponseFactory;
use Artisan\Routing\Interfaces\IApiResponse;
use Exception;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

class ApiService extends Container
{

    private static ApiService $instance;

    /**
     * @throws Exception
     */
    public static function initialize(ApiOptions $apiOptions): static
    {
        return self::$instance = new static($apiOptions);
    }

    public static function i(): ApiService
    {
        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function __construct(ApiOptions $apiOptions)
    {
        parent::__construct();
        $this->initializeApiServices($apiOptions);
    }

    public function isDevelopment(): bool
    {
        return ENVIRONMENT == ApiOptions::ENV_DEVELOPMENT;
    }

    public function getOptions(): ApiOptions
    {
        return $this['api_options'];
    }

    public function getApiResponseFactory(): ApiResponseFactory
    {
        return $this['api_response_factory'];
    }

    public function getContext(): RequestContext
    {
        return $this['context'];
    }

    public function getRequest(): Request
    {
        return $this['request'];
    }

    public function setResponse(IApiResponse $apiResponse): self
    {
        $this['api_response'] = $apiResponse;
        return $this;
    }

    public function getResponse(): IApiResponse
    {
        return $this['api_response'];
    }

    public function setRouteParams(array $routeParams): self
    {
        $this['route_params'] = $routeParams;
        return $this;
    }

    public function getRouteParams(): array
    {
        return $this['route_params'];
    }

    public function getConfig(string $key, $default = null)
    {
        if (isset($this['config'][$key])) {
            return $this['config'][$key];
        }
        return $default;
    }

    public function getUrlBuilder() {
        //TODO
    }

    /**
     * @throws Exception
     */
    private function initializeApiServices(ApiOptions $apiOptions): void
    {
        $this['api_options'] = $apiOptions;

        $config = $this->loadConfiguration($apiOptions->getConfigFile());
        $this['config'] = $config;

        define('ENVIRONMENT', $config['environment']);

        //Request
        $context = new RequestContext();
        $request = Request::createFromGlobals();
        $context->fromRequest($request);
        $this['request'] = $request;
        $this['context'] = $context;

        //Api Response
        $this['api_response_factory'] = function() {
            return new ApiResponseFactory();
        };
    }

    /**
     * @throws Exception
     */
    private function loadConfiguration(string $configFile): array
    {
        return require $configFile;
    }
}
