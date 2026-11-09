<?php

namespace KayiwaRahim\Relworx\Services;

use KayiwaRahim\Relworx\RelworxClient;

class DisbursementService
{
    use PaymentRequestValidator;

    public function __construct(protected RelworxClient $client) {}

    public function send(array $data): array
    {
        $this->validatePaymentRequest($data);
        $data['currency'] = strtoupper($data['currency']);

        return $this->client->post('/mobile-money/send-payment', $data);
    }
}
