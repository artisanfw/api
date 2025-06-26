<?php

namespace Artisan\Routing\Interfaces;

interface IApiResponse
{
    public function getCode(): int;
    public function getContentType(): string;
    public function getCharset(): string;
    public function getPayload(): mixed;
    public function getHeaders(): array;

    public function setCode(int $code): self;
    public function setContentType(string $contentType): self;
    public function setCharset(string $charset): self;
    public function setPayload(mixed $payload): self;
    public function setHeader(string $name, string $value): self;

    public function send();

}