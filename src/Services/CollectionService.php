<?php

namespace KayiwaRahim\Relworx\Services;

use KayiwaRahim\Relworx\RelworxClient;

class CollectionService
{
    use PaymentRequestValidator;

    public function __construct(protected RelworxClient $client) {}

    public function request(array $data): array
    {
        $this->validatePaymentRequest($data);
        $data['currency'] = strtoupper($data['currency']);

        return $this->client->post('/mobile-money/request-payment', $data);
    }
}
