# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-04-21

### Added

- Initial Laravel package structure for MQTT broadcasting.
- `MqttServiceProvider` with Laravel auto-discovery support.
- `mqtt` broadcast driver registration through `BroadcastManager`.
- `MqttBroadcaster` implementation with:
  - channel-to-topic mapping and topic prefix support,
  - JSON payload publishing,
  - QoS validation,
  - clean session and retain handling.
- MQTT client abstraction layer:
  - `MqttClientInterface`,
  - `MqttClientFactoryInterface`,
  - `PhpMqttClientAdapter`,
  - `PhpMqttClientFactory`.
- Install command `mqtt:install`:
  - publishes package config,
  - appends MQTT `.env` defaults,
  - patches `config/broadcasting.php` idempotently.
- Package configuration file: `config/mqtt.php`.
- Package documentation:
  - `README.md`,
  - `RELEASE_CHECKLIST.md`.
- GitHub Actions CI workflow at `.github/workflows/ci.yml`:
  - test matrix (PHP 8.2/8.3/8.4, stable + prefer-lowest),
  - static analysis,
  - code style checks,
  - composer validation.
- Test coverage for:
  - broadcaster auth and publish behavior,
  - MQTT factory config mapping,
  - install command idempotency.

### Changed

- Renamed placeholder test files to descriptive names for broadcaster and install command coverage.
- Updated Composer metadata and dependencies for package readiness.
- Updated PHPUnit configuration source include and schema-compatible settings.

### Known Limitations

- Private and presence channel authentication is currently not supported.
- TLS-specific MQTT connection options are not yet implemented.
