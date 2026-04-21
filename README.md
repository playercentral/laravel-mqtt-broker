# Laravel MQTT Broker

MQTT broadcasting driver for Laravel.

This package adds an `mqtt` broadcasting connection so Laravel events can be published to MQTT topics.

## Features

- Adds an `mqtt` broadcast driver through `BroadcastManager`.
- Provides install command: `php artisan mqtt:install`.
- Publishes package config via `mqtt-config` tag.
- Supports topic prefixing, QoS, retain flag, clean session, and broker connection settings.
- Includes unit and feature test coverage for package wiring and publish behavior.

## Requirements

- PHP `^8.2`
- Laravel components `^12.0`
- MQTT client: `php-mqtt/client ^2.3`

## Installation

Install the package via Composer:

```bash
composer require playercentral/laravel-mqtt-broker
```

Run the installer:

```bash
php artisan mqtt:install
```

Or publish only the config:

```bash
php artisan vendor:publish --provider="PlayerCentral\MqttBroker\MqttServiceProvider" --tag="mqtt-config"
```

## Configuration

The package config file is `config/mqtt.php`.

Example:

```php
return [
    'host' => env('MQTT_HOST', '127.0.0.1'),
    'port' => (int) env('MQTT_PORT', 1883),
    'client_id' => env('MQTT_CLIENT_ID', 'laravel-mqtt-broker'),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'topic_prefix' => env('MQTT_TOPIC_PREFIX', 'laravel/events'),
    'options' => [
        'timeout' => (int) env('MQTT_TIMEOUT', 10),
        'keep_alive' => (int) env('MQTT_KEEP_ALIVE', 60),
        'qos' => (int) env('MQTT_QOS', 0),
        'clean_session' => (bool) env('MQTT_CLEAN_SESSION', true),
        'retain' => (bool) env('MQTT_RETAIN', false),
    ],
];
```

The installer also appends MQTT variables to `.env` and adds a default `mqtt` connection block to `config/broadcasting.php` if missing.

## Broadcasting Connection

Ensure `config/broadcasting.php` includes:

```php
'mqtt' => [
    'driver' => 'mqtt',
    'host' => env('MQTT_HOST', '127.0.0.1'),
    'port' => (int) env('MQTT_PORT', 1883),
    'client_id' => env('MQTT_CLIENT_ID', 'player_central_app'),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'options' => [
        'qos' => (int) env('MQTT_QOS', 0),
        'retain' => env('MQTT_RETAIN', false),
    ],
],
```

Set default broadcaster in `.env`:

```bash
BROADCAST_CONNECTION=mqtt
```

## Behavior Notes

- Event payloads are published as JSON with this shape:
  - `event`: event name
  - `payload`: event payload array
- Topic is derived from channel name, optionally prefixed with `MQTT_TOPIC_PREFIX`.
- Private and presence channel auth is not currently supported by this package.

## Known Limitations

This package is in its initial release (v0.1.0) and has some limitations that may be addressed in future versions:

- **Private and Presence Channels**: Authentication for private and presence channels is not supported. Attempting to authenticate these channel types will result in an exception. This is due to the nature of MQTT's pub/sub model, which differs from traditional WebSocket broadcasting.

- **TLS/SSL Connections**: TLS-specific connection options (such as certificate-based authentication, custom CA certificates, or secure connections) are not yet implemented. Only basic username/password authentication over unencrypted connections is supported.

If your application requires these features, consider using alternative broadcasting drivers or contributing to extend this package.s

This package is in its initial release (v0.1.0) and has some limitations that may be addressed in future versions:

- **Private and Presence Channels**: Authentication for private and presence channels is not supported. Attempting to authenticate these channel types will result in an exception. This is due to the nature of MQTT's pub/sub model, which differs from traditional WebSocket broadcasting.

- **TLS/SSL Connections**: TLS-specific connection options (such as certificate-based authentication, custom CA certificates, or secure connections) are not yet implemented. Only basic username/password authentication over unencrypted connections is supported.

If your application requires these features, consider using alternative broadcasting drivers or contributing to extend this package.

The package currently uses two test layers:

- Unit tests:
  - broadcaster auth behavior
  - topic/payload publishing rules
  - QoS validation
  - MQTT client factory config mapping
- Feature tests:
  - `mqtt:install` command behavior
  - idempotency for `.env` and `config/broadcasting.php` patching

Run tests:

```bash
vendor/bin/pest
```

Run static checks:

```bash
vendor/bin/pint
vendor/bin/phpstan analyse
```

## Security

If you discover a security issue, please open a private report to the maintainers before creating a public issue.

## License

MIT
