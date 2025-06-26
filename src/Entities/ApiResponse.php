<?php

namespace Artisan\Routing\Entities;

use Artisan\Routing\Interfaces\IApiResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse implements IApiResponse
{
    private int $code = Response::HTTP_OK;
    private string $contentType = 'text/html';
    private string $charset = 'UTF-8';
    private mixed $payload = null;
    private array $headers = [];
    protected bool $sended = false;

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): ApiResponse
    {
        $this->code = $code;
        return $this;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): ApiResponse
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function setCharset(string $charset): ApiResponse
    {
        $this->charset = $charset;
        return $this;
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    public function setPayload(mixed $payload): ApiResponse
    {
        $this->payload = $payload;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeader(string $name, string $value): ApiResponse
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function isSended(): bool
    {
        return $this->sended;
    }

    /**
     * Use this method to complete the payload or add headers before send.
     */
    protected function beforeSend(): void
    {

    }

    public function send(): ApiResponse
    {
        if (!$this->sended) {
            $this->beforeSend();
            $contentType = $this->getContentType();
            if (str_starts_with($contentType, 'text/')) {
                $contentType .= '; charset=' . $this->getCharset();
            }
            $this->setHeader('Content-Type', $contentType);
            $payload = is_array($this->getPayload()) ? serialize($this->getPayload()) : $this->getPayload();
            $r = new Response($payload, $this->getCode(), $this->getHeaders());
            $r->send();
        }
        $this->sended = true;
        return $this;
    }


}