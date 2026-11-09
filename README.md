# Laravel Relworx

A Laravel package for Relworx mobile money collections, disbursements, wallet balance and transaction history.

> Initial implementation scaffold. Verify API credentials and test in a Relworx-approved test environment before processing live funds.

## Requirements

- PHP 8.1+
- Laravel 10, 11, 12 and 13 (subject to the PHP version required by each Laravel release)
- A Relworx business account, API key and account number

## Installation

```bash
composer require kayiwarahim/laravel-relworx
php artisan vendor:publish --tag=relworx-config
```

Add to `.env`:

```dotenv
RELWORX_API_KEY=your_api_key
RELWORX_ACCOUNT_NO=your_business_account_number
RELWORX_BASE_URL=https://payments.relworx.com/api
RELWORX_TIMEOUT=30
RELWORX_CONNECT_TIMEOUT=10

# Optional webhook support
RELWORX_WEBHOOKS_ENABLED=true
RELWORX_WEBHOOK_PATH=relworx/webhook
RELWORX_WEBHOOK_URL=https://your-domain.example/relworx/webhook
RELWORX_WEBHOOK_SECRET=your_business_webhook_authentication_key
RELWORX_WEBHOOK_VERIFY_SIGNATURE=true
RELWORX_WEBHOOK_TIMESTAMP_TOLERANCE=300
```

## Usage

```php
use KayiwaRahim\\Relworx\\Facades\\Relworx;

$collection = Relworx::collections()->request([
    'reference' => 'ORDER-100001', // 8-36 characters; unique per transaction
    'msisdn' => '+256701345672',
    'currency' => 'UGX',
    'amount' => 10000,
    'description' => 'Order payment',
]);

$payout = Relworx::disbursements()->send([
    'reference' => 'PAYOUT-100001',
    'msisdn' => '+256701345672',
    'currency' => 'UGX',
    'amount' => 50000,
    'description' => 'Customer payout',
]);

$balance = Relworx::balance()->get('UGX');
$status = Relworx::transactions()->status($collection['internal_reference']);
$history = Relworx::transactions()->history();
```

## Important behaviour

- Payment amounts and phone numbers are validated locally. Phone numbers must be in international E.164-style format, for example `+256701345678`; currency codes are normalized to uppercase.
- The package does not enforce country-specific minimum or maximum payment amounts. Relworx applies its own limits and can reject an otherwise valid request.
- Relworx currently rate-limits collection initiation to 5 requests per 10 minutes per mobile number. Avoid repeatedly creating new collection requests for the same customer.
- If a payment POST fails at the connection layer, `RelworxException::outcomeUnknown()` is `true`. Do not blindly repeat the payment; first query transaction status/history or reconcile with Relworx.
- Collection and payout initiation are asynchronous. An accepted request is not proof that money moved; check the returned `internal_reference` or consume Relworx webhooks.
- Do not automatically retry collection or payout POST requests after timeouts. The provider may have accepted the request even if the client did not receive the response.
- Generate a unique customer reference for every payment attempt (8-36 characters).
- Store API credentials only in server-side environment/configuration and never log API keys or sensitive request data.
- Relworx transaction history is limited by the provider to the previous 30 days and up to 1,000 transactions.
- The balance endpoint reports the Relworx business account wallet balance for the selected currency.

## Webhooks

When enabled, the package registers `POST /relworx/webhook` by default (configurable with `RELWORX_WEBHOOK_PATH`). Configure the exact public callback URL in `RELWORX_WEBHOOK_URL`; it must match the URL entered in your Relworx business account, including the scheme, path, trailing slash and query string. Set `RELWORX_WEBHOOK_SECRET` to the webhook authentication key from the Relworx business account. Signature verification is enabled by default.

The package verifies the `Relworx-Signature` header using Relworx's documented HMAC-SHA256 procedure and checks the timestamp age. It then dispatches these Laravel events:

- `KayiwaRahim\\Relworx\\Events\\RelworxWebhookReceived`
- `KayiwaRahim\\Relworx\\Events\\RelworxPaymentSucceeded`
- `KayiwaRahim\\Relworx\\Events\\RelworxPaymentFailed`

Register listeners in your application and make your transaction updates idempotent using `internal_reference` and your own transaction state. Relworx can retry webhook deliveries up to 10 times with exponential backoff; this package deliberately does not assume a database or cache store and therefore does not claim durable exactly-once processing. Return HTTP 200 only after your application has safely accepted the event for processing. If signature verification is disabled, the endpoint accepts unsigned requests and should only be used for controlled local testing.

## Compatibility testing

GitHub Actions runs the test suite against the matching Orchestra Testbench lines for Laravel 10, 11, 12 and 13. Laravel 10 uses PHP 8.1; newer Laravel versions use their supported PHP runtimes.

## Tests

Install development dependencies and run:

```bash
composer install
composer test
```

Tests use Laravel HTTP fakes and do not move real money.

## Exceptions

Failed HTTP responses throw `KayiwaRahim\\Relworx\\Exceptions\\RelworxException`. The exception exposes `httpStatus()`, `responseBody()`, `relworxCode()` and `outcomeUnknown()`. The last method is particularly important after a payment initiation request loses its connection before a response is received.

## API

The implementation follows the Relworx API v2 header and documented mobile-money endpoints. See https://payments.relworx.com/docs.

## License

MIT
