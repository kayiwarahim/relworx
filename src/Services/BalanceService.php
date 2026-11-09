<?php

namespace KayiwaRahim\Relworx\Services;

use InvalidArgumentException;
use KayiwaRahim\Relworx\RelworxClient;

class BalanceService
{
    public function __construct(protected RelworxClient $client) {}

    public function get(string $currency = 'UGX'): array
    {
        $currency = strtoupper(trim($currency));
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('The currency must be a three-letter ISO currency code.');
        }

        return $this->client->get('/mobile-money/check-wallet-balance', ['currency' => $currency]);
    }
}
