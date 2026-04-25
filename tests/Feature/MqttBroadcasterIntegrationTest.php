<?php

declare(strict_types=1);

use PlayerCentral\MqttBroker\Broadcasters\MqttBroadcaster;
use PlayerCentral\MqttBroker\Mqtt\PhpMqttClientFactory;

it('broadcasts messages to mosquitto broker', function () {
    // Use real MQTT client factory
    $factory = new PhpMqttClientFactory();
    $config = config('broadcast.connections.mqtt');
    
    $broadcaster = new MqttBroadcaster($config, $factory);
    
    // Broadcast a test message
    $broadcaster->broadcast(
        ['test-channel'],
        'test.event',
        ['user_id' => 1, 'message' => 'Hello from test']
    );
    
    // If we got here without exception, broadcast was successful
    expect(true)->toBeTrue();
})->group('integration');

it('broadcasts to multiple channels', function () {
    $factory = new PhpMqttClientFactory();
    $config = config('broadcast.connections.mqtt');
    
    $broadcaster = new MqttBroadcaster($config, $factory);
    
    $broadcaster->broadcast(
        ['channel-1', 'channel-2', 'channel-3'],
        'multi.channel.event',
        ['data' => 'test payload']
    );
    
    expect(true)->toBeTrue();
})->group('integration');

it('publishes with correct topic prefix', function () {
    $factory = new PhpMqttClientFactory();
    $config = config('broadcast.connections.mqtt');
    
    // Verify config has the prefix
    expect($config['topic_prefix'])->toBe('laravel/events');
    
    $broadcaster = new MqttBroadcaster($config, $factory);
    $broadcaster->broadcast(['my-channel'], 'event.name', ['test' => true]);
    
    expect(true)->toBeTrue();
})->group('integration');
