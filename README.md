# Laravel MQTT Broker

MQTT broadcasting driver for Laravel.

This package adds an `mqtt` broadcasting connection so Laravel events can be published to MQTT topics.

> **Note:** This is version 0.1.0 - an initial release focused on public channel broadcasting. See [Known Limitations](#known-limitations) for current restrictions.

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

## Development Setup

### Local MQTT Testing

To run a local MQTT broker with Docker for testing:

```bash
docker-compose up -d
```

The broker will be accessible at `localhost:1883`.

### TLS Testing (Optional)

To test TLS connections locally, generate self-signed test certificates:

```bash
bash .docker/mosquitto/generate-certs.sh
```

This creates the following test certificates in `.docker/mosquitto/certs/`:
- `ca.crt` - Certificate Authority
- `server.crt` & `server.key` - Server certificates
- `client.crt` & `client.key` - Client certificates

**Important:** These are test certificates only and should never be used in production.

To enable TLS in your local Mosquitto broker, uncomment the TLS listener section in `.docker/mosquitto/config/mosquitto.conf` and restart:

```bash
docker-compose restart
```

The TLS broker will then be accessible at `localhost:8883`.

Configure your Laravel app to use TLS:

```bash
MQTT_PORT=8883
MQTT_TLS_ENABLED=true
MQTT_TLS_CA_CERTIFICATE=/path/to/.docker/mosquitto/certs/ca.crt
MQTT_TLS_CERTIFICATE=/path/to/.docker/mosquitto/certs/client.crt
MQTT_TLS_KEY=/path/to/.docker/mosquitto/certs/client.key
MQTT_TLS_VERIFY_PEER=true
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
        'tls' => [
            'enabled' => (bool) env('MQTT_TLS_ENABLED', false),
            'certificate' => env('MQTT_TLS_CERTIFICATE'),  // Client certificate file path
            'key' => env('MQTT_TLS_KEY'),                   // Client key file path
            'ca_certificate' => env('MQTT_TLS_CA_CERTIFICATE'),  // CA certificate file path
            'verify_peer' => (bool) env('MQTT_TLS_VERIFY_PEER', true),
            'verify_peer_name' => (bool) env('MQTT_TLS_VERIFY_PEER_NAME', true),
            'self_signed_allowed' => (bool) env('MQTT_TLS_SELF_SIGNED_ALLOWED', false),
        ],
    ],
];
```

The installer also appends MQTT variables to `.env` and adds a default `mqtt` connection block to `config/broadcasting.php` if missing.

## Usage

### Broadcasting Events

Use Laravel's standard broadcasting system with the MQTT driver:

```php
// Via Broadcast facade
Broadcast::channel('my-channel')->broadcast(new MyEvent($data));

// Via Mqtt facade (direct access)
use PlayerCentral\MqttBroker\Facades\Mqtt;

Mqtt::broadcast(['my-channel'], 'user.created', [
    'user_id' => 123,
    'name' => 'John Doe'
]);
```

### Configuration

Set your broadcast driver in `.env`:

```env
BROADCAST_DRIVER=mqtt
MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_USERNAME=your_username
MQTT_PASSWORD=your_password
MQTT_TOPIC_PREFIX=laravel/events
```

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
        'tls' => [
            'enabled' => (bool) env('MQTT_TLS_ENABLED', false),
            'certificate' => env('MQTT_TLS_CERTIFICATE'),
            'key' => env('MQTT_TLS_KEY'),
            'ca_certificate' => env('MQTT_TLS_CA_CERTIFICATE'),
            'verify_peer' => (bool) env('MQTT_TLS_VERIFY_PEER', true),
            'verify_peer_name' => (bool) env('MQTT_TLS_VERIFY_PEER_NAME', true),
            'self_signed_allowed' => (bool) env('MQTT_TLS_SELF_SIGNED_ALLOWED', false),
        ],
    ],
],
```

Set default broadcaster in `.env`:

```bash
BROADCAST_CONNECTION=mqtt
```

## TLS Configuration

To enable secure connections to your MQTT broker, set the following environment variables:

```bash
MQTT_TLS_ENABLED=true
MQTT_TLS_CERTIFICATE=/path/to/client.crt     # Client certificate file
MQTT_TLS_KEY=/path/to/client.key              # Client key file
MQTT_TLS_CA_CERTIFICATE=/path/to/ca.crt       # CA certificate file
MQTT_TLS_VERIFY_PEER=true                     # Verify broker certificate
MQTT_TLS_VERIFY_PEER_NAME=true                # Verify certificate hostname
MQTT_TLS_SELF_SIGNED_ALLOWED=false            # Allow self-signed certificates
```

**Note:** When using TLS, ensure your MQTT broker is configured to listen on the TLS port (typically 8883).

## Behavior Notes

- Event payloads are published as JSON with this shape:
  - `event`: event name
  - `payload`: event payload array
- Topic is derived from channel name, optionally prefixed with `MQTT_TOPIC_PREFIX`.
- Private and presence channel auth is not currently supported by this package.

## Known Limitations

This package is in its initial release (v0.1.0) and has some limitations:

- **Private and Presence Channels**: Authentication for private and presence channels is not supported. Attempting to authenticate these channel types will result in an exception. This is due to the nature of MQTT's pub/sub model, which differs from traditional WebSocket broadcasting.

If your application requires this feature, consider using alternative broadcasting drivers or contributing to extend this package.

## Testing Strategy

The package currently uses two test layers:

- Unit tests:
  - broadcaster auth behavior
  - topic/payload publishing rules
  - QoS validation
  - MQTT client factory config mapping
  - TLS configuration handling
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
