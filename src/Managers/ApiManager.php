<?php

namespace Artisan\Routing\Managers;

use Artisan\Routing\Entities\ApiOptions;
use Artisan\Routing\Exceptions\AuthorizationRequiredException;
use Artisan\Routing\Exceptions\BadRequestException;
use Artisan\Routing\Exceptions\ForbiddenException;
use Artisan\Routing\Exceptions\HttpException;
use Artisan\Routing\Exceptions\MethodNotAllowedException;
use Artisan\Routing\Interfaces\IApiResponse;
use Artisan\Routing\Interfaces\IAuthenticationStrategy;
use Artisan\Routing\Interfaces\IMiddleware;
use Artisan\Routing\Interfaces\IRouter;
use Artisan\Routing\Services\ApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;


class ApiManager
{
    /** @var IRouter[] */
    private array $routers = [];

    private Request $request;
    private IApiResponse $response;
    private ?IAuthenticationStrategy $authStrategy = null;

    /** @var IMiddleware[] */
    private array $preProcessMiddleware = [];
    /** @var IMiddleware[] */
    private array $postProcessMiddleware = [];

    public function __construct()
    {
        $this->sendCORSHeaders();
        $request = ApiService::i()->getRequest();
        if ($request->getMethod() == 'OPTIONS') exit;
    }

    public function setAuthStrategy(IAuthenticationStrategy $authStrategy): ApiManager
    {
        $this->authStrategy = $authStrategy;
        return $this;
    }

    private function sendCORSHeaders(): void
    {
        $apiOptions = ApiService::i()->getOptions();
        $allowedHosts = $apiOptions->getAllowedHosts();
        $allowedMethods = implode(', ', $apiOptions->getAllowedMethods());
        $allowedHeaders = implode(', ', $apiOptions->getAllowedHeaders());

        if (!empty($_SERVER['HTTP_REFERER'])) {
            $host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            $protocol = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME);
            $port = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PORT);
        } elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
            $host = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
            $protocol = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_SCHEME);
            $port = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_PORT);
        } else {
            return;
        }

        $origin = $protocol . '://' . $host .($port? ':'.$port : '');

        if (!in_array($host, $allowedHosts) || in_array('*', $allowedHosts)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        header('Access-Control-Allow-Methods: '.$allowedMethods);
        header('Access-Control-Allow-Headers: '.$allowedHeaders);
        header('Access-Control-Allow-Credentials: true');
    }

    public function addPreProcessor(IMiddleware $preProcessor): ApiManager
    {
        $this->preProcessMiddleware[$preProcessor::class] = $preProcessor;
        return $this;
    }

    public function addPostProcessor(IMiddleware $postProcessor): ApiManager
    {
        $this->postProcessMiddleware[$postProcessor::class] = $postProcessor;
        return $this;
    }

    public function addRouter(IRouter $router): self
    {
        $this->routers[$router::class] = $router;
        return $this;
    }

    public function processRequest(Request $request): void
    {
        $this->instanceApiResponse();

        try {
            $this->checkApiKey($request);
            $this->checkMethod($request);
            $this->checkRequestContentType($request);

            foreach ($this->routers as $router) {
                $params = [];
                $routes = $router->getRoutes();
                $matcher = new UrlMatcher($routes, ApiService::i()->getContext());

                try {
                    $params = $matcher->match(ApiService::i()->getRequest()->getPathInfo());
                    $request->attributes->add($this->reduceParams($params));
                    ApiService::i()->setRouteParams($params);
                } catch (ResourceNotFoundException $e) {
                    continue;
                }

                if ($params) {
                    $this->runController($params);
                }
            }

            $this->response->setCode(404);
            $this->response->send();
        } catch (HttpException $e) {
            $this->sendError($e->getCode(), $e->getMessage());
        }
    }

    private function instanceApiResponse(): void
    {
        $responseType = ApiService::i()->getOptions()->getResponseType();
        $this->response = ApiService::i()->getApiResponseFactory()->getResponseInstance($responseType);
        ApiService::i()->setResponse($this->response);
    }

    /**
     * @throws ForbiddenException
     */
    private function checkApiKey(Request $request): void
    {
        $acceptedApiKeys = ApiService::i()->getOptions()->getAcceptedApiKeys();

        if (in_array('*', $acceptedApiKeys)) return;

        if (!$request->headers->has('X-API-KEY') || !in_array($request->headers->get('X-API-KEY'), $acceptedApiKeys)) {
            throw new ForbiddenException('Incorrect API key');
        }
    }

    /**
     * @throws MethodNotAllowedException
     */
    private function checkMethod(Request $request): void
    {
        $allowedMethods = ApiService::i()->getOptions()->getAllowedMethods();
        if (!in_array(strtoupper($request->getMethod()), $allowedMethods)) {
            throw new MethodNotAllowedException();
        }
    }

    /**
     * @throws BadRequestException
     */
    private function checkRequestContentType(Request $request): void
    {
        $acceptedContentType = ApiService::i()->getOptions()->getRequestType();
        $contentType = $request->headers->get('Content-Type', 'unknown');
        if ($request->getMethod() !== 'GET' && !str_ends_with($acceptedContentType, $contentType)) {
            throw new BadRequestException('Incorrect Content-Type');
        }
    }

    private function reduceParams(array $params): array
    {
        return array_filter($params, fn($value, $key) => !str_starts_with($key, '_'), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @throws MethodNotAllowedException
     * @throws AuthorizationRequiredException
     */
    private function runController(array $routeParams): void
    {
        $request = ApiService::i()->getRequest();

        if (!in_array($request->getMethod(), $routeParams['_methods'])) {
            throw new MethodNotAllowedException($request->getMethod());
        }

        list($namespace, $classFunction) = $routeParams['_controller'];

        $controller = new $namespace();

        if ($this->authStrategy && isset($routeParams['_authRequired']) && $routeParams['_authRequired']) {
            $this->authStrategy->authenticate();
        }

        $response = ApiService::i()->getResponse();

        foreach ($this->preProcessMiddleware as $middleware) {
            $middleware->before($routeParams, $request, $response);
        }

        $controller->{$classFunction}($request, $response);

        foreach ($this->postProcessMiddleware as $middleware) {
            $middleware->after($routeParams, $request, $response);
        }

        $this->response->send();
    }

    protected function sendError(int $httpCode, string $message): void
    {
        $responseType = ApiService::i()->getOptions()->getResponseType();

        if (ApiOptions::RESPONSE_JSON === $responseType) {
            $this->response->setPayload(['success' => false, 'error' => $message]);
        } elseif (ApiOptions::RESPONSE_XML === $responseType) {
            $this->response->setPayload("<success>false</success><error>$message</error>");
        } else {
            $this->response->setPayload($message);
        }

        $this->response->setCode($httpCode);
        $this->response->send();
        exit;
    }
}
