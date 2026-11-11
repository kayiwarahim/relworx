# Contributing

Thank you for considering contributing to Laravel Relworx.

## Before you start

- Search existing issues and pull requests before opening a new one.
- For security vulnerabilities, follow [SECURITY.md](SECURITY.md); do not publish exploit details in a public issue.
- For API behaviour, include the relevant Relworx documentation reference where possible. Never include API keys, webhook secrets, customer phone numbers or other personal/payment data.

## Development setup

1. Fork and clone the repository.
2. Install dependencies with `composer install`.
3. Create a focused branch, for example `fix/webhook-validation` or `feat/transaction-filtering`.
4. Make the smallest change that addresses the issue.
5. Add or update tests.

## Quality checks

Run these before opening a pull request:

```bash
composer validate --no-check-publish
composer test
```

If you change package behaviour, update the README or relevant documentation. Tests must use HTTP fakes and must not initiate real payments.

## Pull requests

Include:
- A clear explanation of the problem and solution.
- Tests added or updated.
- Any relevant compatibility impact.
- Documentation changes, if applicable.

Keep pull requests focused and avoid unrelated formatting changes.

## Release versioning

Use Semantic Versioning:
- Patch: backward-compatible bug fixes.
- Minor: backward-compatible functionality.
- Major: breaking public API or behaviour changes.

Update `CHANGELOG.md`, ensure CI passes, create a version tag, and publish a GitHub release. See [docs/releasing.md](docs/releasing.md).
