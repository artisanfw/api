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

    public function errorAuthorizationRequired(string $message = ''): void;
    public function errorBadRequest(string $message = ''): void;
    public function errorForbidden(string $message = ''): void;
    public function errorHttp(string $message = ''): void;
    public function errorIAmATeapot(string $message = ''): void;
    public function errorInternalServerError(string $message = ''): void;
    public function errorMethodNotAllowed(string $message = ''): void;
    public function errorNotFound(string $message = ''): void;
    public function errorNotImplemented(string $message = ''): void;
    public function errorPaymentRequired(string $message = ''): void;
    public function errorServiceTemporarilyUnavailable(string $message = ''): void;
    public function errorTooManyRequests(string $message = ''): void;
    public function errorUnavailableForLegalReasons(string $message = ''): void;
    public function errorUnsupportedMediaType(string $message = ''): void;
}