<?php

namespace Artisan\Routing\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface IMiddleware
{
    public function run(array $routeParams, Request $request, IApiResponse $response): void;
}