# Routing Component
This component is a complete API system capable of receiving HTTP requests and returning responses in various formats.

## Requirements
PHP 8.3+

## Installation
```bash
  composer require artisanfw/api
```
## Getting Started

**1.** Create a `Bootstrap.php` file in your `src/` folder and add the following code:

```php
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
**2.** Call the `run` method of the `Bootstrap` class in your `index.php` (example below):
```php
<?php
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

(new Api\Bootstrap)->run(); 
```
**3.** Create the routes you need and add them to the ApiManager within the Bootstrap.
```php
$apiManager = new ApiManager();
$apiManager->addRouter(new RouterList());
```
You can add different routers to organize separate areas of your project, or use a single Router class.

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
You can configure the headers that are accepted. The API provides a predefined set of common headers, but you can define your own as needed.
```php
$apiOptions->setAllowedHeaders(ApiOptions::COMMON_CORS_HEADERS)
```
### Methods
These methods define which HTTP verbs are allowed in the routes. For example, if only `GET` is accepted, no route can implement a `POST` method.
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
The API supports a configuration file in PHP format, which must return an associative array of parameters. This file uses keys to define the configurations for each service.
```php
<?php
$apiOptions->setConfigFile(PROJECT_DIR . '/config.php');
```
By convention, the `PROJECT_DIR` constant must be defined in the Bootstrap file. Other Artisan services use this constant to navigate through the project.

## ApiManager
The ApiManager is responsible for managing the routes and processing the request.
```php
$apiManager = new ApiManager();
// ... ApiManager setters ...
$apiManager->processRequest(ApiService::i()->getRequest());
```

### Authentication
The API supports authentication through the `_authRequired` parameter in each route. If this parameter is set to `true`, the request must be authenticated.

The API uses the `Artisan\Routing\Interfaces\IAuthenticationStrategy` interface to authenticate the request.
You can create your own authentication strategy and set it in the ApiManager.
```php
$apiManager->setAuthStrategy(new YourAuthenticationStrategy());
```

### Middlewares
You can call a preprocessor or postprocessor for each request.
```php
$apiManager->setPreprocessor(new YourPreprocessor());
$apiManager->setPostprocessor(new YourPostprocessor());
```
The pre/post processors use the `Artisan\Routing\Interfaces\IMiddleware` interface and are applied to all incoming requests.

* Preprocessors are executed `before` the controller is called.
* Postprocessors are executed `after` the controller is called.

You can code a class that works as pre and post processor. The methods `before` and `after` determines if a Middleware is a pre or post processor.  

## ApiService
The ApiService is a container for the ApiOptions, Context, Request, etc.

### ApiOptions
In case you need to access the ApiOptions, you can access using:
```php
$apiOptions = ApiService::i()->getApiOptions();
```
Usually you don't need to access the ApiOptions directly.

### Context
In case you need to access the Context. You can access it using:
```php
$context = ApiService::i()->getContext();
```
Usually, it’s better to use the Request instead of the Context.

### Configuration
You can access the configuration file set in the ApiOptions using:
```php
$config = ApiService::i()->getConfig();
```
The configuration is an associative array of parameters.

### Request
Request is a class that contains information about the request. You can access it using:
```php
$request = ApiService::i()->getRequest();
```
### Response
Response is a class that contains information about the response.
```php
$response = ApiService::i()->getResponse();
```

## Controllers
Think of controllers as the endpoints of your API.. They are responsible for processing the request and returning the response.
Controllers receive two parameters: `IApiRequest` and `IApiResponse`

Here an example:
```php
<?php
namespace Api\Controllers;

use Artisan\Routing\Interfaces\IApiRequest;
use Artisan\Routing\Interfaces\IApiResponse;

class HomeController
{
    public function landing(IApiRequest $request, IApiResponse $response): void
    {
        $response->setPayload(['message' => 'Hello World!']);
    }
}
```
