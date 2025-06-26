# Routing Component
This component is a complete API capable of receiving HTTP requests and returning responses in various formats.

## Requirements
PHP 8.3+

## Installation
```bash
  composer require artisanfw/routing
```
## Getting Started
1. Create a `Bootstrap.php` file in your `src/` folder and add the following code:
```php
<?php
namespace Api;

use Artisan\Home\Routers\HomeRouter;
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
            $apiManager->addRouter(new RouterList());

            $apiManager->processRequest(ApiService::i()->getRequest());
        } catch (\Throwable $t) {
            error_log($t);
            $response = new Response(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->send();
        }
    }
}
```
2. Call the `run` method of the Bootstrap class in your index.php (example below):
```php
<?php
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

(new Api\Bootstrap)->run(); 
```
3. Create the routes you need and add them to the ApiManager within the Bootstrap.
```php
$apiManager = new ApiManager();
$apiManager->addRouter(new RouterList());
```
You can add different Routers to separate areas of your project or use a single route class.

## Routers
To create a Router, create a class that implements the `Artisan\Routing\Interfaces\IRouter` interface.
```php
<?php

namespace Api\Routers;

use Artisan\Routing\Interfaces\IRouter;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouterList implements IRouter
{
    public function getRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('home', new Route('/', [
            '_authRequired' => false,
            '_methods' => ['GET'],
            '_controller' => [YourHomeController::class, 'landing'], //HomeController class, landing method
        ]));
        
        //Add other routes

        return $routes;
    }
}
```

## ApiOptions
The ApiOptions class has different configurations you can modify for your project.

### Label
The label is an identifier in case there is more than one Bootstrap in the project.
```php
$apiOptions->setLabel('artisan_api_example');
```

### Request Type
You can configure the type of request that is accepted. In the Bootstrap example, JSON format is accepted.
```php
$apiOptions->setRequestType(ApiOptions::REQUEST_JSON);
```

### Response Type
You can configure the type of response that is returned. In the Bootstrap example, JSON format is returned.
```php
$apiOptions->setResponseType(ApiOptions::RESPONSE_JSON);
```
The response is automatically encoded before being sent from an array.

### Headers
You can configure the headers that are accepted. The API already provides an array of common headers, but you can define the ones you need.
```php
$apiOptions->setAllowedHeaders(ApiOptions::COMMON_CORS_HEADERS)
```
### Methods
The methods accepted here are those that can be used in the routes. For example, if only `GET` is accepted, no route can implement a `POST` method.
```php
$apiOptions->setAllowedMethods(ApiOptions::COMMON_CORS_METHODS);
```
### Accepted Api Keys
If your project is limited to certain applications connecting to the API, you can define the accepted API Keys.

This is also useful for recognizing the origin of different platforms (mobile, web, etc).
```php
$apiOptions->setAcceptedApiKeys(['api_key_1', 'api_key_2']);
```
API Keys must be sent in the `X-API-KEY` header of the request.

### Allowed Hosts
If your project is limited to certain hosts connecting to the API, you can define the accepted hosts.
```php
$apiOptions->setAllowedHosts(['localhost', 'domain.com']);
```
## Service Configuration
The API supports a configuration file in php format that must return an associative array of parameters. This file uses keys to define the configurations for each service.
```php
<?php
$apiOptions->setConfigFile(PROJECT_DIR . '/config.php');
```
By convention, the `PROJECT_DIR` constant must be defined in the Bootstrap file. Other Artisan services use this constant to navigate through the project.