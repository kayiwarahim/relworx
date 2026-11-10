# Laravel integration example

This guide shows how an application can consume the package's webhook events. Adapt the persistence logic to your application's own payment model and database schema.

## 1. Create a listener

For example, create `app/Listeners/HandleRelworxPaymentWebhook.php`:

```php
<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use KayiwaRahim\Relworx\Events\RelworxPaymentFailed;
use KayiwaRahim\Relworx\Events\RelworxPaymentSucceeded;

class HandleRelworxPaymentWebhook
{
    public function handle(RelworxPaymentSucceeded|RelworxPaymentFailed $event): void
    {
        $payload = $event->payload;
        $reference = $payload['internal_reference'] ?? null;

        if (! is_string($reference) || $reference === '') {
            return;
        }

        DB::transaction(function () use ($reference, $payload, $event) {
            // Replace this query with your application's payment model/table.
            $payment = DB::table('payments')
                ->where('relworx_internal_reference', $reference)
                ->lockForUpdate()
                ->first();

            if (! $payment || in_array($payment->status, ['succeeded', 'failed'], true)) {
                return; // Unknown payment or already-finalized event: do not apply twice.
            }

            $newStatus = $event instanceof RelworxPaymentSucceeded
                ? 'succeeded'
                : 'failed';

            DB::table('payments')
                ->where('id', $payment->id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

            // Apply your business-side balance/order change here, inside this
            // transaction, only when transitioning from a non-final state.
        });
    }
}
```

The example assumes a `payments` table with `relworx_internal_reference`, `status`, `id` and `updated_at` columns. Adjust it to your schema. Ensure the reference column has a database unique index. Do not treat a webhook as permission to trust an arbitrary amount or customer ID; reconcile the event against the payment you originally created.

## 2. Register the listener

In applications using an event service provider, register the listener for both package events:

```php
protected $listen = [
    \KayiwaRahim\Relworx\Events\RelworxPaymentSucceeded::class => [
        \App\Listeners\HandleRelworxPaymentWebhook::class,
    ],
    \KayiwaRahim\Relworx\Events\RelworxPaymentFailed::class => [
        \App\Listeners\HandleRelworxPaymentWebhook::class,
    ],
];
```

If your Laravel application uses event discovery or a different event-registration convention, register the same listener using that application's approach.

## 3. Enable webhook routing

Set the webhook environment variables described in the main README, then clear/rebuild Laravel's configuration cache. Confirm the route exists:

```bash
php artisan route:list --name=relworx.webhook
```

Register the exact HTTPS callback URL and matching webhook authentication key in your Relworx business account.

## 4. Production checklist

- Verify signatures; do not disable verification in production.
- Use HTTPS.
- Store the provider internal reference and your own unique payment reference.
- Enforce database uniqueness and idempotent state transitions.
- Do not credit/debit a wallet more than once.
- Persist or queue work safely before returning HTTP 200.
- Log a correlation/reference ID, not secrets or full sensitive payloads.
- Reconcile payments whose final state is unknown.
