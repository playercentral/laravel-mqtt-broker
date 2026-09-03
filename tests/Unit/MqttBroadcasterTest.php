<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use PlayerCentral\MqttBroker\Broadcasters\MqttBroadcaster;
use PlayerCentral\MqttBroker\Tests\Fakes\FakeMqttClient;
use PlayerCentral\MqttBroker\Tests\Fakes\FakeMqttClientFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

it('authenticates public channels and rejects presence channels', function () {
    $broadcaster = new MqttBroadcaster([], new FakeMqttClientFactory(new FakeMqttClient()));

    $publicRequest = Request::create('/broadcasting/auth', 'POST', ['channel_name' => 'orders.1']);
    $presenceRequest = Request::create('/broadcasting/auth', 'POST', ['channel_name' => 'presence-team.1']);

    expect($broadcaster->auth($publicRequest))->toBe(['authenticated' => true]);

    expect(fn () => $broadcaster->auth($presenceRequest))
        ->toThrow(RuntimeException::class, 'Presence channels are not currently supported');
});

it('denies unauthenticated private channel access', function () {
    $broadcaster = new MqttBroadcaster([], new FakeMqttClientFactory(new FakeMqttClient()));

    $privateRequest = Request::create('/broadcasting/auth', 'POST', ['channel_name' => 'private-orders.1']);

    expect(fn () => $broadcaster->auth($privateRequest))
        ->toThrow(AccessDeniedHttpException::class);
});

it('authorizes authenticated private channel requests with signature', function () {
    $broadcaster = new MqttBroadcaster(['key' => 'secret-test-key'], new FakeMqttClientFactory(new FakeMqttClient()));

    $broadcaster->channel('orders.{id}', function ($user, $id) {
        return (int) $id === 42;
    });

    $user = (object) ['id' => 1];
    $request = Request::create('/broadcasting/auth', 'POST', [
        'channel_name' => 'private-orders.42',
        'socket_id' => 'socket_123',
    ]);
    $request->setUserResolver(fn () => $user);

    $response = $broadcaster->auth($request);

    expect($response)->toHaveKeys(['auth', 'channel']);
    expect($response['channel'])->toBe('private-orders.42');
    expect($response['auth'])->toBe(hash_hmac('sha256', 'socket_123:private-orders.42', 'secret-test-key'));
});

it('publishes mqtt payloads with mapped topics', function () {
    $client = new FakeMqttClient();
    $factory = new FakeMqttClientFactory($client);
    $broadcaster = new MqttBroadcaster([
        'topic_prefix' => 'app/events',
        'options' => [
            'qos' => 1,
            'retain' => 'true',
            'clean_session' => 'false',
        ],
    ], $factory);

    $broadcaster->broadcast(['/orders/created', 'payments/settled'], 'OrderCreated', ['id' => 42]);

    expect($client->connected)->toBeTrue();
    expect($client->disconnected)->toBeTrue();
    expect($client->cleanSession)->toBeFalse();
    expect($client->published)->toHaveCount(2);
    expect($client->published[0]['topic'])->toBe('app/events/orders/created');
    expect($client->published[1]['topic'])->toBe('app/events/payments/settled');
    expect($client->published[0]['qos'])->toBe(1);
    expect($client->published[0]['retain'])->toBeTrue();
    expect(json_decode($client->published[0]['message'], true, 512, JSON_THROW_ON_ERROR))
        ->toBe([
            'event' => 'OrderCreated',
            'payload' => ['id' => 42],
        ]);
});

it('rejects invalid qos values', function () {
    $broadcaster = new MqttBroadcaster([
        'options' => [
            'qos' => 3,
        ],
    ], new FakeMqttClientFactory(new FakeMqttClient()));

    expect(fn () => $broadcaster->broadcast(['orders/created'], 'OrderCreated', []))
        ->toThrow(RuntimeException::class, 'Invalid MQTT QoS value');
});
