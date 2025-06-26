<?php

namespace Artisan\Routing\Factories;

use Artisan\Routing\Entities\ApiOptions;
use Artisan\Routing\Entities\ApiRequest;
use Artisan\Routing\Entities\XmlApiRequest;
use Artisan\Routing\Entities\JsonApiRequest;
use Artisan\Routing\Interfaces\IApiRequest;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class ApiRequestFactory
{
    public function getRequestInstance(ApiOptions $options, ?string $requestData = null): IApiRequest
    {
        if (null === $requestData) {
            $requestData = file_get_contents('php://input');
        }

        $json = json_decode($requestData, true);
        $headers = $this->getHeaders();
        $params = array_merge($_POST, $_GET);

        $payload = $this->getPayload($options, $requestData, $json);

        $apiRequest = $this->getApiRequestInstance($options);
        $apiRequest->setHeaders($headers);
        $apiRequest->setParams($params);

        return $apiRequest;
    }

    private function getHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (str_starts_with($key, 'CONTENT_')) {
                $headers[$key] = $value;
            }
        }
        if (function_exists('apache_request_headers')) {
            $apacheHeaders = apache_request_headers();
            foreach ($apacheHeaders as $keyA => $value) {
                $headers[$keyA] = $value;
            }
        }

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'] ?? '';
            $headers['Authorization'] = 'Basic '.base64_encode($user.':'.$password);
        }

        return $headers;
    }

    private function getPayload(ApiOptions $options, ?string $requestData = null, ?array $json = null): array
    {
        if ($options->getRequestType() == ApiOptions::REQUEST_XML) {
            try {
                $xmlEncoder = new XmlEncoder();
                return $xmlEncoder->decode($requestData, XmlEncoder::FORMAT);
            } catch (\Exception $e) {
                return [];
            }
        }

        if ($options->getRequestType() == ApiOptions::REQUEST_JSON) {
            return $json ?? [];
        }

        return [];
    }

    private function getApiRequestInstance(ApiOptions $options): IApiRequest
    {
        if ($options->getRequestType() == ApiOptions::REQUEST_XML) {
            return new XmlApiRequest();
        } elseif ($options->getRequestType() == ApiOptions::REQUEST_JSON) {
            return new JsonApiRequest();
        }
        return new ApiRequest();
    }

}