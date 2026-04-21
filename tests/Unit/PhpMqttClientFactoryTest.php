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
            int $keepAlive,
            array $tlsConfig = []
        ): ConnectionSettings {
            $this->capturedUsername = $username;
            $this->capturedPassword = $password;
            $this->capturedTimeout = $timeout;
            $this->capturedKeepAlive = $keepAlive;

            return parent::makeConnectionSettings($username, $password, $timeout, $keepAlive, $tlsConfig);
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

it('applies tls configuration when enabled', function () {
    $factory = new class() extends PhpMqttClientFactory
    {
        public mixed $capturedTlsEnabled = null;

        public mixed $capturedTlsCertificate = null;

        public mixed $capturedTlsKey = null;

        public mixed $capturedTlsCaCertificate = null;

        public mixed $capturedTlsVerifyPeer = null;

        public mixed $capturedTlsVerifyPeerName = null;

        public mixed $capturedTlsSelfSignedAllowed = null;

        protected function makeClient(string $host, int $port, string $clientId): MqttClient
        {
            return new MqttClient('127.0.0.1', 1883, 'test-client');
        }

        protected function makeConnectionSettings(
            mixed $username,
            mixed $password,
            int $timeout,
            int $keepAlive,
            array $tlsConfig = []
        ): ConnectionSettings {
            $this->capturedTlsEnabled = $tlsConfig['enabled'] ?? false;
            $this->capturedTlsCertificate = $tlsConfig['certificate'] ?? null;
            $this->capturedTlsKey = $tlsConfig['key'] ?? null;
            $this->capturedTlsCaCertificate = $tlsConfig['ca_certificate'] ?? null;
            $this->capturedTlsVerifyPeer = $tlsConfig['verify_peer'] ?? true;
            $this->capturedTlsVerifyPeerName = $tlsConfig['verify_peer_name'] ?? true;
            $this->capturedTlsSelfSignedAllowed = $tlsConfig['self_signed_allowed'] ?? false;

            return parent::makeConnectionSettings($username, $password, $timeout, $keepAlive, $tlsConfig);
        }
    };

    $factory->make([
        'host' => 'mqtt.example.com',
        'port' => 8883,
        'client_id' => 'secure-app',
        'username' => 'mqtt-user',
        'password' => 'mqtt-pass',
        'options' => [
            'timeout' => 10,
            'keep_alive' => 60,
            'tls' => [
                'enabled' => true,
                'certificate' => '/path/to/client.crt',
                'key' => '/path/to/client.key',
                'ca_certificate' => '/path/to/ca.crt',
                'verify_peer' => true,
                'verify_peer_name' => true,
                'self_signed_allowed' => false,
            ],
        ],
    ]);

    expect($factory->capturedTlsEnabled)->toBeTrue();
    expect($factory->capturedTlsCertificate)->toBe('/path/to/client.crt');
    expect($factory->capturedTlsKey)->toBe('/path/to/client.key');
    expect($factory->capturedTlsCaCertificate)->toBe('/path/to/ca.crt');
    expect($factory->capturedTlsVerifyPeer)->toBeTrue();
    expect($factory->capturedTlsVerifyPeerName)->toBeTrue();
    expect($factory->capturedTlsSelfSignedAllowed)->toBeFalse();
});

it('applies default tls settings when disabled', function () {
    $factory = new class() extends PhpMqttClientFactory
    {
        public mixed $capturedTlsEnabled = null;

        public mixed $capturedTlsVerifyPeer = null;

        public mixed $capturedTlsSelfSignedAllowed = null;

        protected function makeClient(string $host, int $port, string $clientId): MqttClient
        {
            return new MqttClient('127.0.0.1', 1883, 'test-client');
        }

        protected function makeConnectionSettings(
            mixed $username,
            mixed $password,
            int $timeout,
            int $keepAlive,
            array $tlsConfig = []
        ): ConnectionSettings {
            $this->capturedTlsEnabled = $tlsConfig['enabled'] ?? false;
            $this->capturedTlsVerifyPeer = $tlsConfig['verify_peer'] ?? true;
            $this->capturedTlsSelfSignedAllowed = $tlsConfig['self_signed_allowed'] ?? false;

            return parent::makeConnectionSettings($username, $password, $timeout, $keepAlive, $tlsConfig);
        }
    };

    $factory->make([
        'host' => 'mqtt.example.com',
        'port' => 1883,
        'client_id' => 'app',
        'username' => null,
        'password' => null,
        'options' => [
            'timeout' => 10,
            'keep_alive' => 60,
        ],
    ]);

    expect($factory->capturedTlsEnabled)->toBeFalse();
    expect($factory->capturedTlsVerifyPeer)->toBeTrue();
    expect($factory->capturedTlsSelfSignedAllowed)->toBeFalse();
});

it('supports self-signed certificates with verification disabled', function () {
    $factory = new class() extends PhpMqttClientFactory
    {
        public mixed $capturedTlsSelfSignedAllowed = null;

        public mixed $capturedTlsVerifyPeer = null;

        protected function makeClient(string $host, int $port, string $clientId): MqttClient
        {
            return new MqttClient('127.0.0.1', 1883, 'test-client');
        }

        protected function makeConnectionSettings(
            mixed $username,
            mixed $password,
            int $timeout,
            int $keepAlive,
            array $tlsConfig = []
        ): ConnectionSettings {
            $this->capturedTlsSelfSignedAllowed = $tlsConfig['self_signed_allowed'] ?? false;
            $this->capturedTlsVerifyPeer = $tlsConfig['verify_peer'] ?? true;

            return parent::makeConnectionSettings($username, $password, $timeout, $keepAlive, $tlsConfig);
        }
    };

    $factory->make([
        'host' => 'mqtt.example.com',
        'port' => 8883,
        'client_id' => 'app',
        'options' => [
            'tls' => [
                'enabled' => true,
                'ca_certificate' => '/path/to/self-signed.crt',
                'verify_peer' => false,
                'self_signed_allowed' => true,
            ],
        ],
    ]);

    expect($factory->capturedTlsSelfSignedAllowed)->toBeTrue();
    expect($factory->capturedTlsVerifyPeer)->toBeFalse();
});
