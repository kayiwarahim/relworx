<?php

namespace KayiwaRahim\Relworx\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use KayiwaRahim\Relworx\Events\RelworxPaymentFailed;
use KayiwaRahim\Relworx\Events\RelworxPaymentSucceeded;
use KayiwaRahim\Relworx\Events\RelworxWebhookReceived;
use KayiwaRahim\Relworx\Webhooks\RelworxWebhookVerifier;

class RelworxWebhookController extends Controller
{
    public function __invoke(Request $request, RelworxWebhookVerifier $verifier): JsonResponse
    {
        $config = config('relworx.webhooks', []);
        $url = (string) ($config['url'] ?? '');
        $secret = (string) ($config['secret'] ?? '');

        if (($config['verify_signature'] ?? true) && !$verifier->verify(
            $request,
            $url,
            $secret,
            (int) ($config['timestamp_tolerance'] ?? 300)
        )) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $postData = $request->post();
        $payload = array_intersect_key($postData, array_flip(['status', 'customer_reference', 'internal_reference']));
        if (count($payload) !== 3 || !is_string($payload['status'] ?? null)) {
            return response()->json(['message' => 'Invalid webhook payload.'], 422);
        }

        event(new RelworxWebhookReceived($payload));

        $status = strtolower($payload['status']);
        if ($status === 'success') {
            event(new RelworxPaymentSucceeded($payload));
        } elseif ($status === 'failed') {
            event(new RelworxPaymentFailed($payload));
        }

        return response()->json(['message' => 'Webhook received.'], 200);
    }
}
