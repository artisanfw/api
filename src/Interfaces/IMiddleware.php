<?php

namespace Artisan\Routing\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface IMiddleware
{
    public function before(array $routeParams, Request $request, IApiResponse $response): void;
    public function after(array $routeParams, Request $request, IApiResponse $response): void;
}