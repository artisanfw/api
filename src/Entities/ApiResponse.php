<?php

namespace Artisan\Routing\Entities;

use Artisan\Routing\Exceptions\AuthorizationRequiredException;
use Artisan\Routing\Exceptions\BadRequestException;
use Artisan\Routing\Exceptions\ForbiddenException;
use Artisan\Routing\Exceptions\HttpException;
use Artisan\Routing\Exceptions\IAmATeapotException;
use Artisan\Routing\Exceptions\InternalServerErrorException;
use Artisan\Routing\Exceptions\MethodNotAllowedException;
use Artisan\Routing\Exceptions\NotFoundException;
use Artisan\Routing\Exceptions\NotImplementedException;
use Artisan\Routing\Exceptions\PaymentRequiredException;
use Artisan\Routing\Exceptions\ServiceTemporarilyUnavailableException;
use Artisan\Routing\Exceptions\TooManyRequestsException;
use Artisan\Routing\Exceptions\UnavailableForLegalReasonsException;
use Artisan\Routing\Exceptions\UnsupportedMediaTypeException;
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

    /**
     * @throws AuthorizationRequiredException
     */
    public function errorAuthorizationRequired(string $message = ''): void
    {
        throw new AuthorizationRequiredException($message);
    }

    /**
     * @throws BadRequestException
     */
    public function errorBadRequest(string $message = ''): void
    {
        throw new BadRequestException($message);
    }

    /**
     * @throws ForbiddenException
     */
    public function errorForbidden(string $message = ''): void
    {
        throw new ForbiddenException($message);
    }

    /**
     * @throws HttpException
     */
    public function errorHttp(string $message = ''): void
    {
        throw new HttpException($message);
    }

    /**
     * @throws IAmATeapotException
     */
    public function errorIAmATeapot(string $message = ''): void
    {
        throw new IAmATeapotException($message);
    }

    /**
     * @throws InternalServerErrorException
     */
    public function errorInternalServerError(string $message = ''): void
    {
        throw new InternalServerErrorException($message);
    }

    /**
     * @throws MethodNotAllowedException
     */
    public function errorMethodNotAllowed(string $message = ''): void
    {
        throw new MethodNotAllowedException($message);
    }

    /**
     * @throws NotFoundException
     */
    public function errorNotFound(string $message = ''): void
    {
        throw new NotFoundException($message);
    }

    /**
     * @throws NotImplementedException
     */
    public function errorNotImplemented(string $message = ''): void
    {
        throw new NotImplementedException($message);
    }

    /**
     * @throws PaymentRequiredException
     */
    public function errorPaymentRequired(string $message = ''): void
    {
        throw new PaymentRequiredException($message);
    }

    /**
     * @throws ServiceTemporarilyUnavailableException
     */
    public function errorServiceTemporarilyUnavailable(string $message = ''): void
    {
        throw new ServiceTemporarilyUnavailableException($message);
    }

    /**
     * @throws TooManyRequestsException
     */
    public function errorTooManyRequests(string $message = ''): void
    {
        throw new TooManyRequestsException($message);
    }

    /**
     * @throws UnavailableForLegalReasonsException
     */
    public function errorUnavailableForLegalReasons(string $message = ''): void
    {
        throw new UnavailableForLegalReasonsException($message);
    }

    /**
     * @throws UnsupportedMediaTypeException
     */
    public function errorUnsupportedMediaType(string $message = ''): void
    {
        throw new UnsupportedMediaTypeException($message);
    }
}