# Release procedure

Use this checklist for each package release.

## 1. Prepare the release

- [ ] Confirm the intended changes are merged into the default branch.
- [ ] Run `composer validate --no-check-publish`.
- [ ] Run `composer test` on the supported PHP/Laravel matrix through GitHub Actions.
- [ ] Review dependency/security alerts and resolve or document outstanding risks.
- [ ] Verify the README examples and supported-version claims.
- [ ] Update `CHANGELOG.md`.
- [ ] Check that no credentials, customer data or local files are included.

## 2. Choose the version

Follow Semantic Versioning:
- Patch release for compatible fixes: `1.0.1`.
- Minor release for backward-compatible features: `1.1.0`.
- Major release for breaking changes: `2.0.0`.

## 3. Tag and publish

After the release commit is on the default branch:

```bash
git checkout main
git pull --ff-only
git tag -a vX.Y.Z -m "Release vX.Y.Z"
git push origin vX.Y.Z
```

Create a GitHub release for the tag and include the relevant changelog section. Confirm Packagist detects the new version. If automatic updates are configured, verify the repository webhook is working; otherwise trigger an update from Packagist.

## 4. Verify the published version

In a clean Laravel application:

```bash
composer clear-cache
composer require kayiwarahim/relworx:^X.Y
composer show kayiwarahim/relworx
```

Check the installed version, package discovery, config publishing and the documented integration path. Never test live money movement without explicit approval and controlled test arrangements.
