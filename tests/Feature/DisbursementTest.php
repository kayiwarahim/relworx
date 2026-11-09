<?php

namespace KayiwaRahim\Relworx\Tests\Feature;

use Illuminate\Support\Facades\Http;
use KayiwaRahim\Relworx\Facades\Relworx;
use KayiwaRahim\Relworx\Tests\TestCase;

class DisbursementTest extends TestCase
{
    public function test_it_sends_a_disbursement_request(): void
    {
        Http::fake([
            'payments.relworx.com/api/mobile-money/send-payment' => Http::response([
                'success' => true,
                'internal_reference' => 'rw-payout-123',
            ], 200),
        ]);

        $result = Relworx::disbursements()->send([
            'reference' => 'PAYOUT-100001',
            'msisdn' => '+256701345672',
            'currency' => 'UGX',
            'amount' => 50000,
            'description' => 'Test payout',
        ]);

        $this->assertSame('rw-payout-123', $result['internal_reference']);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/mobile-money/send-payment')
            && $request['account_no'] === 'TEST-ACCOUNT'
            && $request['amount'] === 50000);
    }
}
