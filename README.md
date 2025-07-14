# Api Component
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
        define('PROJECT_DIR', dirname(__DIR__));

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
$apiOptions->setConfigFile(PROJECT_DIR . '/.config.php');
```
By convention, the `PROJECT_DIR` constant must be defined in the Bootstrap file. Other Artisan services use this constant to navigate through the project.

You can access the configuration settings using:
```php
$data = Config::get($key, $fallbackOptional);
```

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
You can call a preprocessor for each request.
```php
$apiManager->setMiddleware(new YourMiddleware());
```
The pre processors use the `Artisan\Routing\Interfaces\IMiddleware` interface and are applied to all incoming requests.

Middleware are executed `after` the Authentication and `before` the controller is called.

## ApiService
The ApiService is a container for the ApiOptions, Context, Request, etc.

### ApiOptions
In case you need to access the ApiOptions, you can access using:
```php
$apiOptions = ApiService::i()->getApiOptions();
```
Usually, you don't need to access the ApiOptions directly.

### Context
In case you need to access the Context. You can access it using:
```php
$context = ApiService::i()->getContext();
```

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
### Environment
If your code needs to process logic that depends on the environment, you can check if it's a development environment using:
```php
 ApiService::i()->isDevelopment();
```
You can also get the name of the environment using the `ENVIRONMENT` constant.
If you've created a configuration file with the `environment` key, you can assign custom environment names.
In the absence of an environment configuration, it will always be considered a **development** environment by default.

## Controllers
Think of controllers as the endpoints of your API. They are responsible for processing the request and returning the response.
Controllers receive two parameters: `Request` and `IApiResponse`

Here an example:
```php
<?php
namespace Api\Controllers;

use Artisan\Routing\Interfaces\IApiResponse;
use Symfony\Component\HttpFoundation\Request;

class HomeController
{
    public function landing(Request $request, IApiResponse $response): void
    {
        $response->setPayload(['message' => 'Hello World!']);
    }
}
```
## Sending Errors
All user-facing errors are represented as Exceptions. The API layer is responsible for converting these exceptions into HTTP responses with the corresponding status codes.
You may provide an explanatory message when throwing an exception:
```php
throw new \Artisan\Routing\Exceptions\BadRequestException('All fields are required');
```

For convenience, a predefined set of common HTTP error responses is available through the IApiResponse interface, allowing you to return standardized error responses directly:
```php
$response->errorPaymentRequired();
$response->errorUnsupportedMediaType();
$response->errorBadRequest('All fields are required');
...
```

