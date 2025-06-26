<?php

namespace Artisan\Routing\Interfaces;

interface IApiRequest
{
    public function getUri(): string;
    public function getHeaders(): array;
    public function getMethod(): string;
    public function getParams(): array;
}