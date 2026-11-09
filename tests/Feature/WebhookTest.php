<?php

namespace KayiwaRahim\Relworx\Tests\Feature;

use Illuminate\Support\Facades\Event;
use KayiwaRahim\Relworx\Events\RelworxPaymentSucceeded;
use KayiwaRahim\Relworx\Events\RelworxWebhookReceived;
use KayiwaRahim\Relworx\Tests\TestCase;

class WebhookTest extends TestCase
{
    public function test_it_accepts_a_valid_signed_webhook_and_dispatches_events(): void
    {
        Event::fake();

        $url = 'https://merchant.example/relworx/webhook';
        $timestamp = (string) time();
        $payload = [
            'status' => 'success',
            'customer_reference' => 'ORDER-100001',
            'internal_reference' => 'relworx-123',
        ];
        $signed = $url . $timestamp . 'customer_reference' . $payload['customer_reference']
            . 'internal_reference' . $payload['internal_reference'] . 'status' . $payload['status'];
        $signature = hash_hmac('sha256', $signed, 'webhook-secret');

        $response = $this->call('POST', '/relworx/webhook', $payload, [], [], [
            'HTTP_RELWORX_SIGNATURE' => "t={$timestamp},v={$signature}",
        ]);

        $response->assertOk();
        Event::assertDispatched(RelworxWebhookReceived::class);
        Event::assertDispatched(RelworxPaymentSucceeded::class);
    }

    public function test_it_rejects_an_invalid_signature(): void
    {
        $response = $this->call('POST', '/relworx/webhook', [
            'status' => 'success',
            'customer_reference' => 'ORDER-100001',
            'internal_reference' => 'relworx-123',
        ], [], [], ['HTTP_RELWORX_SIGNATURE' => 't=' . time() . ',v=not-valid']);

        $response->assertUnauthorized();
    }
}
