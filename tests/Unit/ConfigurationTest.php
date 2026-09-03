<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

it('loads mqtt broadcast configuration', function () {
    $config = Config::get('broadcasting.connections.mqtt');

    expect($config)
        ->toBeArray()
        ->toHaveKey('driver', 'mqtt')
        ->toHaveKey('host')
        ->toHaveKey('port')
        ->toHaveKey('client_id')
        ->toHaveKey('username')
        ->toHaveKey('password')
        ->toHaveKey('topic_prefix')
        ->toHaveKey('options');
});

it('has correct default values for mqtt broadcast', function () {
    $config = Config::get('broadcasting.connections.mqtt');

    expect($config['driver'])->toBe('mqtt');
    expect($config['host'])->toBe('127.0.0.1');
    expect($config['port'])->toBe(1883);
    expect($config['topic_prefix'])->toBe('laravel/events');
    expect($config['options']['qos'])->toBe(0);
    expect($config['options']['clean_session'])->toBeTrue();
    expect($config['options']['retain'])->toBeFalse();
    expect($config['options']['tls']['enabled'])->toBeFalse();
});

it('allows configuration to be overridden', function () {
    Config::set('broadcasting.connections.mqtt.host', 'custom.broker.com');
    Config::set('broadcasting.connections.mqtt.port', 8883);

    expect(Config::get('broadcasting.connections.mqtt.host'))->toBe('custom.broker.com');
    expect(Config::get('broadcasting.connections.mqtt.port'))->toBe(8883);
});

it('supports multiple mqtt connections', function () {
    Config::set('broadcasting.connections.mqtt_secondary', [
        'driver' => 'mqtt',
        'host' => 'secondary.broker.com',
        'port' => 1884,
        'client_id' => 'secondary-client',
    ]);

    expect(Config::get('broadcasting.connections.mqtt_secondary.driver'))->toBe('mqtt');
    expect(Config::get('broadcasting.connections.mqtt_secondary.host'))->toBe('secondary.broker.com');
});
