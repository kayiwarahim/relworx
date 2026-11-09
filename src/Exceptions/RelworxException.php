<?php

namespace KayiwaRahim\Relworx\Exceptions;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use RuntimeException;

class RelworxException extends RuntimeException
{
    public function __construct(
        string $message,
        protected ?int $httpStatus = null,
        protected array $responseBody = [],
        protected ?string $relworxCode = null,
        protected bool $outcomeUnknown = false,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus ?? 0, $previous);
    }

    public static function fromResponse(Response $response, bool $paymentRequest = false): self
    {
        $body = $response->json();
        $body = is_array($body) ? $body : ['raw' => $response->body()];
        $message = $body['message'] ?? $body['error'] ?? 'Relworx API request failed.';
        $message = is_scalar($message) ? (string) $message : 'Relworx API request failed.';

        return new self(
            $message,
            $response->status(),
            $body,
            isset($body['code']) ? (string) $body['code'] : null,
            $paymentRequest && ($response->status() >= 500 || $response->status() >= 300 && $response->status() < 400),
        );
    }

    public static function connectionFailure(ConnectionException $exception, bool $outcomeUnknown): self
    {
        $message = $outcomeUnknown
            ? 'Could not confirm the Relworx payment request outcome because the connection failed. Check transaction status before attempting another payment.'
            : 'Could not connect to Relworx to retrieve information.';

        return new self($message, null, [], null, $outcomeUnknown, $exception);
    }

    public function httpStatus(): ?int { return $this->httpStatus; }
    public function responseBody(): array { return $this->responseBody; }
    public function relworxCode(): ?string { return $this->relworxCode; }

    /**
     * True when a connection failure occurred after a payment POST was attempted.
     * The provider may have processed the request even though no response arrived.
     */
    public function outcomeUnknown(): bool { return $this->outcomeUnknown; }
}
