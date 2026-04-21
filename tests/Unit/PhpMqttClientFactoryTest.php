<?php

declare(strict_types=1);

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use PlayerCentral\MqttBroker\Mqtt\PhpMqttClientAdapter;
use PlayerCentral\MqttBroker\Mqtt\PhpMqttClientFactory;

it('maps config values into client and connection settings', function () {
    $factory = new class() extends PhpMqttClientFactory
    {
        public string $capturedHost = '';

        public int $capturedPort = 0;

        public string $capturedClientId = '';

        public mixed $capturedUsername = null;

        public mixed $capturedPassword = null;

        public int $capturedTimeout = 0;

        public int $capturedKeepAlive = 0;

        protected function makeClient(string $host, int $port, string $clientId): MqttClient
        {
            $this->capturedHost = $host;
            $this->capturedPort = $port;
            $this->capturedClientId = $clientId;

            return new MqttClient('127.0.0.1', 1883, 'test-client');
        }

        protected function makeConnectionSettings(
            mixed $username,
            mixed $password,
            int $timeout,
            int $keepAlive
        ): ConnectionSettings {
            $this->capturedUsername = $username;
            $this->capturedPassword = $password;
            $this->capturedTimeout = $timeout;
            $this->capturedKeepAlive = $keepAlive;

            return parent::makeConnectionSettings($username, $password, $timeout, $keepAlive);
        }
    };

    $client = $factory->make([
        'host' => 'mqtt.example.com',
        'port' => '2883',
        'client_id' => 'player-app',
        'username' => 'mqtt-user',
        'password' => 'mqtt-pass',
        'options' => [
            'timeout' => '15',
            'keep_alive' => '45',
        ],
    ]);

    expect($client)->toBeInstanceOf(PhpMqttClientAdapter::class);
    expect($factory->capturedHost)->toBe('mqtt.example.com');
    expect($factory->capturedPort)->toBe(2883);
    expect($factory->capturedClientId)->toBe('player-app');
    expect($factory->capturedUsername)->toBe('mqtt-user');
    expect($factory->capturedPassword)->toBe('mqtt-pass');
    expect($factory->capturedTimeout)->toBe(15);
    expect($factory->capturedKeepAlive)->toBe(45);
});
