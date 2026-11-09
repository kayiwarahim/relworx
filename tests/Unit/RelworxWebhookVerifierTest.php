<?php

namespace KayiwaRahim\Relworx\Tests\Unit;

use Illuminate\Http\Request;
use KayiwaRahim\Relworx\Tests\TestCase;
use KayiwaRahim\Relworx\Webhooks\RelworxWebhookVerifier;

class RelworxWebhookVerifierTest extends TestCase
{
    public function test_it_verifies_a_valid_relworx_signature(): void
    {
        $url = 'https://merchant.example/api/relworx/webhook';
        $timestamp = (string) time();
        $payload = [
            'status' => 'success',
            'customer_reference' => 'ORDER-100001',
            'internal_reference' => 'relworx-123',
        ];
        $signed = $url . $timestamp . 'customer_reference' . $payload['customer_reference']
            . 'internal_reference' . $payload['internal_reference'] . 'status' . $payload['status'];
        $signature = hash_hmac('sha256', $signed, 'webhook-secret');

        $request = Request::create('/api/relworx/webhook', 'POST', $payload, [], [], [
            'HTTP_RELWORX_SIGNATURE' => "t={$timestamp},v={$signature}",
        ]);

        $this->assertTrue((new RelworxWebhookVerifier())->verify($request, $url, 'webhook-secret'));
    }

    public function test_it_rejects_an_invalid_signature(): void
    {
        $request = Request::create('/webhook', 'POST', [
            'status' => 'success',
            'customer_reference' => 'ORDER-100001',
            'internal_reference' => 'relworx-123',
        ], [], [], ['HTTP_RELWORX_SIGNATURE' => 't=' . time() . ',v=invalid']);

        $this->assertFalse((new RelworxWebhookVerifier())->verify(
            $request,
            'https://merchant.example/webhook',
            'webhook-secret'
        ));
    }
}
