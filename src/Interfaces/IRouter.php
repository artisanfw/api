<?php

namespace Artisan\Routing\Interfaces;

use Symfony\Component\Routing\RouteCollection;

interface IRouter
{
    public function getRoutes(): RouteCollection;
}