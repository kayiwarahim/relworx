<?php

namespace KayiwaRahim\Relworx;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use KayiwaRahim\Relworx\Exceptions\RelworxException;

class RelworxClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $accountNo;
    protected int $timeout;
    protected int $connectTimeout;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->accountNo = (string) ($config['account_no'] ?? '');
        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->connectTimeout = (int) ($config['connect_timeout'] ?? 10);

        if ($this->baseUrl === '' || $this->apiKey === '' || $this->accountNo === '') {
            throw new InvalidArgumentException('Relworx configuration requires base_url, api_key and account_no.');
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL) || !in_array(parse_url($this->baseUrl, PHP_URL_SCHEME), ['https', 'http'], true)) {
            throw new InvalidArgumentException('Relworx base_url must be a valid HTTP or HTTPS URL.');
        }

        if ($this->timeout < 1 || $this->connectTimeout < 1 || $this->connectTimeout > $this->timeout) {
            throw new InvalidArgumentException('Relworx timeouts must be positive and connect_timeout cannot exceed timeout.');
        }
    }

    protected function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->accept('application/vnd.relworx.v2')
            ->asJson()
            ->withToken($this->apiKey)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            // Never follow a redirect with an authenticated payment request.
            ->withOptions(['allow_redirects' => false]);
    }

    public function accountNo(): string
    {
        return $this->accountNo;
    }

    public function post(string $uri, array $payload = []): array
    {
        try {
            $response = $this->request()->post($uri, array_merge($payload, ['account_no' => $this->accountNo]));
        } catch (ConnectionException $exception) {
            throw RelworxException::connectionFailure($exception, true);
        }

        return $this->decode($response, true);
    }

    public function get(string $uri, array $query = []): array
    {
        try {
            $response = $this->request()->get($uri, array_merge($query, ['account_no' => $this->accountNo]));
        } catch (ConnectionException $exception) {
            throw RelworxException::connectionFailure($exception, false);
        }

        return $this->decode($response);
    }

    protected function decode(Response $response, bool $paymentRequest = false): array
    {
        if (!$response->successful()) {
            throw RelworxException::fromResponse($response, $paymentRequest);
        }

        $data = $response->json();
        if (!is_array($data)) {
            throw new RelworxException('Relworx returned an invalid JSON response.', $response->status());
        }

        return $data;
    }
}
