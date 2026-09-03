# Release Checklist (v0.2.1)

Use this checklist to keep releases predictable and repeatable.

## 1) Package Readiness

- [x] `composer.json` metadata is complete (`description`, `keywords`, `license`, `support`, `homepage`).
- [x] Laravel provider auto-discovery entry is present.
- [x] Public API and config keys are stable enough for a tagged release.
- [x] README install/config/usage steps match real package behavior.

## 2) Quality Gates

- [x] Format check passes (`vendor/bin/pint --test`).
- [x] Static analysis passes (`vendor/bin/phpstan analyse`).
- [x] Test suite passes (`vendor/bin/pest --exclude-group=integration`).
- [x] `composer validate --strict` passes.

## 3) CI Matrix Recommendation

Recommended GitHub Actions matrix for this package:

- PHP: `8.2`, `8.3`, `8.4`
- Dependency mode:
  - normal
  - prefer-lowest

Job set:

- `tests` (Pest)
- `static-analysis` (Larastan/PHPStan)
- `code-style` (Pint in test mode)
- `composer-validate` (Strict validation)

## 4) Changelog and Versioning

- [x] Add `CHANGELOG.md`.
- [x] Add release entry for `v0.2.1` with notable changes and known limitations.
- [x] Confirm semantic versioning policy for future changes.

## 5) Tag and Publish

- [ ] Create release commit with docs/tests/metadata updates.
- [ ] Tag `v0.2.1`.
- [ ] Push branch and tag.
- [ ] Create GitHub Release notes from changelog highlights.

## 6) Post-Release Follow-Up

- [ ] Open follow-up issues for:
  - private/presence auth support
  - TLS broker options
  - additional integration tests with a real broker in CI (optional nightly job)
