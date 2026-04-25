<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [
        'mqtt' => [
            'driver' => 'mqtt',
            'host' => env('MQTT_HOST', '127.0.0.1'),
            'port' => (int) env('MQTT_PORT', 1883),
            'client_id' => env('MQTT_CLIENT_ID', 'laravel-mqtt-broker'),
            'username' => env('MQTT_USERNAME') ?: null,
            'password' => env('MQTT_PASSWORD') ?: null,
            'topic_prefix' => env('MQTT_TOPIC_PREFIX', 'laravel/events'),
            'options' => [
                'timeout' => (int) env('MQTT_TIMEOUT', 10),
                'keep_alive' => (int) env('MQTT_KEEP_ALIVE', 60),
                'qos' => (int) env('MQTT_QOS', 0), // 0, 1, or 2
                'clean_session' => (bool) env('MQTT_CLEAN_SESSION', true),
                'retain' => (bool) env('MQTT_RETAIN', false),
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
        'default' => env('BROADCAST_DRIVER', 'mqtt'),
    ],
];
