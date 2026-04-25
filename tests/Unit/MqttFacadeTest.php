<?php

declare(strict_types=1);

use PlayerCentral\MqttBroker\Facades\Mqtt;
use PlayerCentral\MqttBroker\Tests\Fakes\FakeMqttClient;
use PlayerCentral\MqttBroker\Tests\Fakes\FakeMqttClientFactory;

it('provides a facade for mqtt broadcasting', function () {
    // Swap the factory with a fake one for testing
    $fakeClient = new FakeMqttClient();
    $fakeFactory = new FakeMqttClientFactory($fakeClient);
    
    app()->instance('mqtt.broadcaster', new \PlayerCentral\MqttBroker\Broadcasters\MqttBroadcaster(
        config('broadcast.connections.mqtt'),
        $fakeFactory
    ));

    // Test the facade
    Mqtt::broadcast(['test-channel'], 'test.event', ['data' => 'test']);

    expect($fakeClient->connected)->toBeTrue();
    expect($fakeClient->published)->toHaveCount(1);
    expect($fakeClient->published[0]['topic'])->toBe('laravel/events/test-channel');
})->group('facade');