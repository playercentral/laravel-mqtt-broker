<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | MQTT Broker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define your MQTT broker settings. These settings will be
    | used by the MqttBroadcaster to establish a connection.
    |
    */

    'host' => env('MQTT_HOST', '127.0.0.1'),
    'port' => (int) env('MQTT_PORT', 1883),
    'client_id' => env('MQTT_CLIENT_ID', 'laravel-mqtt-broker'),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'topic_prefix' => env('MQTT_TOPIC_PREFIX', 'laravel/events'),

    'options' => [
        'timeout' => (int) env('MQTT_TIMEOUT', 10),
        'keep_alive' => (int) env('MQTT_KEEP_ALIVE', 60),
        'qos' => (int) env('MQTT_QOS', 0), // 0, 1, or 2
        'clean_session' => (bool) env('MQTT_CLEAN_SESSION', true),
        'retain' => (bool) env('MQTT_RETAIN', false),
    ],
];
