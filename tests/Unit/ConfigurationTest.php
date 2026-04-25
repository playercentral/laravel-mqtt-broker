<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

it('loads mqtt broadcast configuration', function () {
    $config = Config::get('broadcast.connections.mqtt');

    expect($config)
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
    $config = Config::get('broadcast.connections.mqtt');

    expect($config['host'])->toBe('127.0.0.1');
    expect($config['port'])->toBe(1883);
    expect($config['topic_prefix'])->toBe('laravel/events');
});

it('loads mqtt broker configuration', function () {
    $config = Config::get('mqtt');

    expect($config)
        ->toHaveKey('host')
        ->toHaveKey('port')
        ->toHaveKey('client_id')
        ->toHaveKey('options');
});

it('allows configuration to be overridden', function () {
    /** @var \PlayerCentral\MqttBroker\Tests\TestCase $this */
    $this->app['config']->set('mqtt.host', 'custom.broker.com');
    $this->app['config']->set('mqtt.port', 8883);

    expect($this->app['config']->get('mqtt.host'))->toBe('custom.broker.com');
    expect($this->app['config']->get('mqtt.port'))->toBe(8883);
});
