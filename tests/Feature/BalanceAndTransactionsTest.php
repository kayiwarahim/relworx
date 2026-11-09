<?php

namespace KayiwaRahim\Relworx\Tests\Feature;

use Illuminate\Support\Facades\Http;
use KayiwaRahim\Relworx\Facades\Relworx;
use KayiwaRahim\Relworx\Tests\TestCase;

class BalanceAndTransactionsTest extends TestCase
{
    public function test_it_queries_wallet_balance(): void
    {
        Http::fake(['*check-wallet-balance*' => Http::response(['balance' => 250000, 'currency' => 'UGX'])]);

        $result = Relworx::balance()->get('UGX');

        $this->assertSame(250000, $result['balance']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'check-wallet-balance')
            && $request['account_no'] === 'TEST-ACCOUNT');
    }

    public function test_it_queries_transaction_status_and_history(): void
    {
        Http::fake([
            '*check-request-status*' => Http::response(['status' => 'success']),
            '*payment-requests/transactions*' => Http::response(['transactions' => []]),
        ]);

        $status = Relworx::transactions()->status('internal-123');
        $history = Relworx::transactions()->history();

        $this->assertSame('success', $status['status']);
        $this->assertSame([], $history['transactions']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'check-request-status')
            && $request['internal_reference'] === 'internal-123');
    }
}
