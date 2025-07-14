<?php

namespace Artisan\Routing\Entities;

use Artisan\Routing\Exceptions\InternalServerErrorException;

class ApiOptions
{
    const string REQUEST_TEXT = 'req/text/html';
    const string REQUEST_JSON = 'req/application/json';
    const string REQUEST_XML = 'req/application/xml';

    const string RESPONSE_TEXT = 'response_text';
    const string RESPONSE_JSON = 'response_json';
    const string RESPONSE_XML = 'response_xml';

    const string ENV_PRODUCTION = 'production';
    const string ENV_DEVELOPMENT = 'development';

    const array COMMON_CORS_HEADERS = [
        'X-API-KEY',
        'Origin',
        'Content-Type',
        'Accept',
        'Accept-Encoding',
        'Accept-Language',
        'Authorization',
        'Cache-Control',
        'Connection',
        'Set-Cookie',
        'Host',
        'Pragma',
        'Referer',
        'User-Agent',
        'Token',
    ];

    const array COMMON_CORS_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

    private string $label = 'unknown';
    private string $requestType = '';
    private string $responseType = '';
    private array $acceptedApiKeys = ['*'];
    private array $allowedHosts = ['*'];
    private array $allowedMethods = [];
    private array $allowedHeaders = [];
    private string $configFile = '';

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): ApiOptions
    {
        $this->label = $label;
        return $this;
    }

    public function getRequestType(): string
    {
        return $this->requestType;
    }

    public function setRequestType(string $requestType): ApiOptions
    {
        $this->requestType = $requestType;
        return $this;
    }

    public function getResponseType(): string
    {
        return $this->responseType;
    }

    public function setResponseType(string $responseType): ApiOptions
    {
        $this->responseType = $responseType;
        return $this;
    }

    public function getAcceptedApiKeys(): array
    {
        return $this->acceptedApiKeys;
    }

    /**
     * @param string[] $acceptedApiKeys
     */
    public function setAcceptedApiKeys(array $acceptedApiKeys): ApiOptions
    {
        $this->acceptedApiKeys = $acceptedApiKeys;
        return $this;
    }

    public function getAllowedHosts(): array
    {
        return $this->allowedHosts;
    }

    /**
     * @param string[] $allowedHosts
     */
    public function setAllowedHosts(array $allowedHosts): ApiOptions
    {
        $this->allowedHosts = $allowedHosts;
        return $this;
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * @param string[] $allowedMethods
     */
    public function setAllowedMethods(array $allowedMethods): ApiOptions
    {
        $this->allowedMethods = array_map('strtoupper', $allowedMethods);
        return $this;
    }

    public function getAllowedHeaders(): array
    {
        return $this->allowedHeaders;
    }

    /**
     * @param string[] $allowedHeaders
     */
    public function setAllowedHeaders(array $allowedHeaders): ApiOptions
    {
        $this->allowedHeaders = $allowedHeaders;
        return $this;
    }

    /**
     * @throws InternalServerErrorException
     */
    public function setConfigFile(string $configFile): ApiOptions
    {
        if (!file_exists($configFile)) {
            throw new InternalServerErrorException('Configuration file not found!');
        }

        $this->configFile = $configFile;
        return $this;
    }

    public function getConfigFile(): string
    {
        return $this->configFile;
    }
}
