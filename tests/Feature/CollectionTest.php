<?php

namespace KayiwaRahim\Relworx\Tests\Feature;

use Illuminate\Support\Facades\Http;
use KayiwaRahim\Relworx\Facades\Relworx;
use KayiwaRahim\Relworx\Tests\TestCase;

class CollectionTest extends TestCase
{
    public function test_it_sends_a_collection_request_with_authentication_and_account_number(): void
    {
        Http::fake([
            'payments.relworx.com/api/mobile-money/request-payment' => Http::response([
                'success' => true,
                'internal_reference' => 'rw-internal-123',
            ], 200),
        ]);

        $result = Relworx::collections()->request([
            'reference' => 'ORDER-100001',
            'msisdn' => '+256701345672',
            'currency' => 'UGX',
            'amount' => 10000,
            'description' => 'Test order',
        ]);

        $this->assertSame('rw-internal-123', $result['internal_reference']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://payments.relworx.com/api/mobile-money/request-payment'
                && $request->hasHeader('Authorization', 'Bearer test-api-key')
                && $request['account_no'] === 'TEST-ACCOUNT'
                && $request['reference'] === 'ORDER-100001';
        });
    }

    public function test_it_rejects_a_short_customer_reference_before_sending(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Relworx::collections()->request([
            'reference' => 'short',
            'msisdn' => '+256701345672',
            'currency' => 'UGX',
            'amount' => 10000,
        ]);
    }
}
