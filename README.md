# Laravel Relworx

[![Tests](https://github.com/kayiwarahim/relworx/actions/workflows/tests.yml/badge.svg)](https://github.com/kayiwarahim/relworx/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/kayiwarahim/relworx.svg)](https://packagist.org/packages/kayiwarahim/relworx)
[![Total Downloads](https://img.shields.io/packagist/dt/kayiwarahim/relworx.svg)](https://packagist.org/packages/kayiwarahim/relworx)
[![License](https://img.shields.io/packagist/l/kayiwarahim/relworx.svg)](LICENSE)

A Laravel package for integrating Relworx mobile-money collections, disbursements, wallet balance and transaction history.

## Requirements

- PHP 8.1 or later.
- Laravel 10, 11, 12 or 13, subject to the PHP version supported by the selected Laravel release.
- A Relworx business account with API credentials and an account number.

## Installation

Install the package through Composer:

```bash
composer require kayiwarahim/relworx
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=relworx-config
```

The package uses Laravel package auto-discovery. You should not need to register its service provider manually.

## Configuration

Add your credentials to the Laravel application's `.env` file:

```dotenv
RELWORX_API_KEY=your_api_key
RELWORX_ACCOUNT_NO=your_business_account_number
RELWORX_BASE_URL=https://payments.relworx.com/api
RELWORX_TIMEOUT=30
RELWORX_CONNECT_TIMEOUT=10

# Optional webhook integration
RELWORX_WEBHOOKS_ENABLED=false
RELWORX_WEBHOOK_PATH=relworx/webhook
RELWORX_WEBHOOK_URL=https://your-domain.example/relworx/webhook
RELWORX_WEBHOOK_SECRET=your_webhook_authentication_key
RELWORX_WEBHOOK_VERIFY_SIGNATURE=true
RELWORX_WEBHOOK_TIMESTAMP_TOLERANCE=300
```

Keep credentials on the server. Never commit real keys to Git, expose them in frontend code, or include them in logs. After changing production configuration, run `php artisan config:cache` as part of your normal deployment process.

## Usage

Resolve the facade from your Laravel application:

```php
use KayiwaRahim\Relworx\Facades\Relworx;

// Request a mobile-money collection.
$collection = Relworx::collections()->request([
    'reference' => 'ORDER-100001',
    'msisdn' => '+256701345672',
    'currency' => 'UGX',
    'amount' => 10000,
    'description' => 'Order payment',
]);

// Send a mobile-money disbursement.
$payout = Relworx::disbursements()->send([
    'reference' => 'PAYOUT-100001',
    'msisdn' => '+256701345672',
    'currency' => 'UGX',
    'amount' => 50000,
    'description' => 'Customer payout',
]);

// Read wallet balance, transaction status and transaction history.
$balance = Relworx::balance()->get('UGX');
$status = Relworx::transactions()->status($collection['internal_reference']);
$history = Relworx::transactions()->history();
```

The exact response fields are determined by the Relworx API response. Persist the returned `internal_reference` against your own payment record so you can reconcile it later.

## Webhooks

Enable webhook routing in configuration:

```dotenv
RELWORX_WEBHOOKS_ENABLED=true
RELWORX_WEBHOOK_PATH=relworx/webhook
RELWORX_WEBHOOK_URL=https://your-domain.example/relworx/webhook
RELWORX_WEBHOOK_SECRET=your_webhook_authentication_key
RELWORX_WEBHOOK_VERIFY_SIGNATURE=true
```

Configure the same callback URL and webhook authentication key in your Relworx business account. The callback URL must match exactly, including scheme, path, trailing slash and query string.

The package registers a `POST` route at the configured path and verifies the `Relworx-Signature` header when signature verification is enabled. It dispatches:

- `RelworxWebhookReceived` for a valid webhook.
- `RelworxPaymentSucceeded` when the status is `success`.
- `RelworxPaymentFailed` when the status is `failed`.

Each event exposes the verified webhook data through its public `payload` property. See [the Laravel integration example](docs/laravel-integration.md) for a listener example.

**Important:** Your listener must be idempotent. Use the provider's `internal_reference` and your own transaction state to ensure a repeated webhook cannot credit or debit a customer more than once. Acknowledge a webhook with HTTP 200 only after your application has safely accepted it for processing. If you queue processing, persist the event before acknowledging it.

Do not disable signature verification in production. If you change the webhook path, update the callback URL in both your Laravel configuration and the Relworx dashboard.

## Important payment behaviour

- Collection and disbursement requests are asynchronous. An accepted request does not mean the payment has completed.
- Check the transaction status or process a verified webhook before marking a payment as completed.
- A connection failure during a payment POST can leave the outcome unknown: the provider may have accepted the request even though your application did not receive the response.
- Do not blindly retry a payment POST after a timeout or connection failure. Reconcile using the transaction reference/status or contact Relworx before initiating another payment.
- Use a unique customer reference for each payment attempt and follow the provider's reference constraints.
- Phone numbers, amounts and currency values are validated locally, but the provider remains authoritative for account, country, currency and transaction limits.
- The transaction-history endpoint is subject to provider-side date and result limits.

## Error handling

Failed HTTP responses and connection errors are represented by `KayiwaRahim\Relworx\Exceptions\RelworxException`.

Useful methods include:

- `httpStatus()`
- `responseBody()`
- `relworxCode()`
- `outcomeUnknown()`

Treat `outcomeUnknown() === true` as a reconciliation requirement, not as permission to retry the payment automatically. Avoid returning raw provider responses or exception details to public clients.

## Testing

The package test suite uses Laravel HTTP fakes; it does not move real money.

```bash
composer install
composer test
```

The GitHub Actions workflow tests supported PHP/Testbench combinations. API credentials are not required to run the unit tests.

## Troubleshooting

### Laravel does not discover the package

Run:

```bash
composer dump-autoload
php artisan package:discover
php artisan optimize:clear
```

Confirm the package is installed with `composer show kayiwarahim/relworx`.

### Configuration changes are not taking effect

Confirm that the environment variables are present in the application environment, then clear and rebuild the configuration cache:

```bash
php artisan config:clear
php artisan config:cache
```

### Webhook requests return 401

Check that the configured callback URL exactly matches the URL registered with Relworx, that the webhook secret is correct, and that the server receives the original `Relworx-Signature` header without modification.

### A payment request times out

Do not immediately submit the payment again. Use the saved reference to reconcile the request with Relworx first. The provider may have processed it even if the client timed out.

## Documentation and support

- [Relworx API documentation](https://payments.relworx.com/docs)
- [Report a bug](https://github.com/kayiwarahim/relworx/issues/new?template=bug_report.yml)
- [Request a feature](https://github.com/kayiwarahim/relworx/issues/new?template=feature_request.yml)
- [Contributing guide](CONTRIBUTING.md)
- [Security policy](SECURITY.md)
- [Changelog](CHANGELOG.md)

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
