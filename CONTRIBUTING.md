# Contributing

1. Fork the repository and create a focused feature branch.
2. Install dependencies with `composer install`.
3. Add or update tests for every behaviour change.
4. Run `composer validate --no-check-publish` and `composer test`.
5. Keep payment POST operations free of automatic retries. A timeout can leave the transaction outcome unknown.
6. Never commit API keys, business account numbers, webhook secrets, or live transaction data.
7. Open a pull request describing the change and the test evidence.
