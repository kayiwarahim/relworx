<?php

namespace KayiwaRahim\Relworx\Webhooks;

use Illuminate\Http\Request;

class RelworxWebhookVerifier
{
    private const SIGNED_FIELDS = ['status', 'customer_reference', 'internal_reference'];

    public function verify(Request $request, string $webhookUrl, string $secret, int $toleranceSeconds = 300): bool
    {
        if ($secret === '' || $webhookUrl === '') {
            return false;
        }

        $header = (string) $request->header('Relworx-Signature', '');
        $parts = [];

        foreach (explode(',', $header) as $component) {
            $pair = explode('=', trim($component), 2);
            if (count($pair) === 2) {
                $parts[trim($pair[0])] = trim($pair[1]);
            }
        }

        $timestamp = $parts['t'] ?? null;
        $providedSignature = $parts['v'] ?? null;

        if (!is_string($timestamp) || !ctype_digit($timestamp) || !is_string($providedSignature) || $providedSignature === '') {
            return false;
        }

        if ($toleranceSeconds > 0 && abs(time() - (int) $timestamp) > $toleranceSeconds) {
            return false;
        }

        $payload = $request->post();
        $signedParams = [];
        foreach (self::SIGNED_FIELDS as $field) {
            if (!array_key_exists($field, $payload) || !is_scalar($payload[$field])) {
                return false;
            }
            $signedParams[$field] = $payload[$field];
        }

        ksort($signedParams);
        $signedData = $webhookUrl . $timestamp;
        foreach ($signedParams as $key => $value) {
            $signedData .= (string) $key . (string) $value;
        }

        $expected = hash_hmac('sha256', $signedData, $secret);

        return hash_equals($expected, $providedSignature);
    }
}
