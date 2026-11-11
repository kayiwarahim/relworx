# Security Policy

## Supported versions

Security fixes are applied to the latest supported release. Users should upgrade to the latest compatible version.

## Reporting a vulnerability

Please do not report security vulnerabilities in public GitHub issues.

Use GitHub's **Report a vulnerability** option on the repository's Security tab (private vulnerability reporting), if enabled. If that option is unavailable, contact the repository maintainer privately through the contact details on the GitHub profile and request a secure channel.

Include:
- A description and potential impact.
- Affected package version and relevant environment.
- Minimal reproduction steps or a proof of concept that does not access real customer data or funds.

Do not include live API keys, webhook secrets, personal data or payment credentials.

## Credential handling

- Never commit Relworx API keys or webhook secrets.
- Rotate credentials immediately if they may have been exposed.
- Keep signature verification enabled in production.
- Avoid logging authorization headers, full payment payloads or sensitive customer information.
- Reconcile unknown payment outcomes before retrying a payment request.
