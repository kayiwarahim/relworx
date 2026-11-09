<?php

namespace KayiwaRahim\Relworx\Tests\Feature;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use KayiwaRahim\Relworx\Facades\Relworx;
use KayiwaRahim\Relworx\Tests\TestCase;

class PaymentValidationTest extends TestCase
{
    public function test_it_rejects_a_phone_number_that_is_not_in_international_format(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Relworx::collections()->request([
            'reference' => 'ORDER-100002',
            'msisdn' => '0701345672',
            'currency' => 'UGX',
            'amount' => 10000,
        ]);
    }

    public function test_it_rejects_a_non_positive_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Relworx::disbursements()->send([
            'reference' => 'PAYOUT-100002',
            'msisdn' => '+256701345672',
            'currency' => 'UGX',
            'amount' => 0,
        ]);
    }

    public function test_it_normalizes_currency_and_does_not_allow_account_number_override(): void
    {
        Http::fake(['*request-payment' => Http::response(['success' => true])]);

        Relworx::collections()->request([
            'reference' => 'ORDER-100003',
            'msisdn' => '+256701345672',
            'currency' => 'ugx',
            'amount' => 10000,
            'account_no' => 'ATTACKER-ACCOUNT',
        ]);

        Http::assertSent(fn ($request) => $request['currency'] === 'UGX'
            && $request['account_no'] === 'TEST-ACCOUNT');
    }
}
