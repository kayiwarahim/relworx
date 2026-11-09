<?php

namespace KayiwaRahim\Relworx\Services;

use InvalidArgumentException;
use KayiwaRahim\Relworx\RelworxClient;

class TransactionService
{
    public function __construct(protected RelworxClient $client) {}

    public function status(string $internalReference): array
    {
        $internalReference = trim($internalReference);
        if ($internalReference === '') {
            throw new InvalidArgumentException('An internal reference is required.');
        }

        return $this->client->get('/mobile-money/check-request-status', [
            'internal_reference' => $internalReference,
        ]);
    }

    public function history(): array
    {
        return $this->client->get('/payment-requests/transactions');
    }
}
