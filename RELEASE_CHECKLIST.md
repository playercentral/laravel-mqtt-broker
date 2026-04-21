# Release Checklist (v0.1.0)

Use this checklist to keep the first tagged release predictable and repeatable.

## 1) Package Readiness

- [ ] `composer.json` metadata is complete (`description`, `keywords`, `license`, `support`, `homepage`).
- [ ] Laravel provider auto-discovery entry is present.
- [ ] Public API and config keys are stable enough for a tagged release.
- [ ] README install/config/usage steps match real package behavior.

## 2) Quality Gates

- [ ] Format check passes (`vendor/bin/pint`).
- [ ] Static analysis passes (`vendor/bin/phpstan analyse` or Larastan command).
- [ ] Test suite passes (`vendor/bin/pest`).
- [ ] `composer validate` passes.

## 3) CI Matrix Recommendation

Recommended GitHub Actions matrix for this package:

- PHP: `8.2`, `8.3`, `8.4`
- Dependency mode:
  - normal
  - prefer-lowest

Suggested job set:

- `tests` (Pest)
- `static-analysis` (Larastan/PHPStan)
- `format` (Pint in check mode)
- `composer-validate`

## 4) Changelog and Versioning

- [ ] Add `CHANGELOG.md`.
- [ ] Add release entry for `v0.1.0` with notable changes and known limitations.
- [ ] Confirm semantic versioning policy for future changes.

## 5) Tag and Publish

- [ ] Create release commit with docs/tests/metadata updates.
- [ ] Tag `v0.1.0`.
- [ ] Push branch and tag.
- [ ] Create GitHub Release notes from changelog highlights.

## 6) Post-Release Follow-Up

- [ ] Open follow-up issues for:
  - private/presence auth support
  - TLS broker options
  - additional integration tests with a real broker in CI (optional nightly job)
