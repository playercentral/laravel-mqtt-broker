<?php

declare(strict_types=1);

use PlayerCentral\MqttBroker\Auth\MqttAuth;

it('generates and verifies HMAC signatures', function () {
    $signature = MqttAuth::generateSignature('socket_456', 'private-matches.99', 'my-secret');

    expect($signature)->toBeString();
    expect(MqttAuth::verifySignature($signature, 'socket_456', 'private-matches.99', 'my-secret'))->toBeTrue();
    expect(MqttAuth::verifySignature($signature, 'socket_456', 'private-matches.100', 'my-secret'))->toBeFalse();
    expect(MqttAuth::verifySignature($signature, 'socket_different', 'private-matches.99', 'my-secret'))->toBeFalse();
    expect(MqttAuth::verifySignature($signature, 'socket_456', 'private-matches.99', 'wrong-secret'))->toBeFalse();
});

it('generates and verifies signed stateless tokens', function () {
    $token = MqttAuth::generateToken(userId: 42, ttl: 3600, topics: ['laravel/events/users/42/#'], key: 'token-secret');

    expect($token)->toBeString();

    $payload = MqttAuth::verifyToken($token, 'token-secret');

    expect($payload)->toBeArray();
    expect($payload['sub'])->toBe(42);
    expect($payload['topics'])->toBe(['laravel/events/users/42/#']);
    expect($payload['exp'])->toBeGreaterThan(time());
});

it('rejects tampered or expired tokens', function () {
    $token = MqttAuth::generateToken(userId: 42, ttl: -10, key: 'token-secret');

    // Expired
    expect(MqttAuth::verifyToken($token, 'token-secret'))->toBeNull();

    // Wrong key
    $validToken = MqttAuth::generateToken(userId: 42, ttl: 3600, key: 'token-secret');
    expect(MqttAuth::verifyToken($validToken, 'different-key'))->toBeNull();

    // Tampered payload
    $tampered = 'invalid.payload.format';
    expect(MqttAuth::verifyToken($tampered, 'token-secret'))->toBeNull();
});
