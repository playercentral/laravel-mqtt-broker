<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Mqtt;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use PlayerCentral\MqttBroker\Contracts\MqttClientFactoryInterface;
use PlayerCentral\MqttBroker\Contracts\MqttClientInterface;

class PhpMqttClientFactory implements MqttClientFactoryInterface
{
    public function make(array $config): MqttClientInterface
    {
        $host = (string) Arr::get($config, 'host', '127.0.0.1');
        $port = (int) Arr::get($config, 'port', 1883);
        $defaultId = 'laravel-mqtt-'.Str::slug(config('app.name', 'default')).'-'.Str::random(5);
        $clientId = (string) Arr::get($config, 'client_id', $defaultId);
        $username = Arr::get($config, 'username');
        $password = Arr::get($config, 'password');
        $timeout = (int) Arr::get($config, 'options.timeout', 10);
        $keepAlive = (int) Arr::get($config, 'options.keep_alive', 60);

        $connectionSettings = $this->makeConnectionSettings(
            $username,
            $password,
            $timeout,
            $keepAlive
        );

        return new PhpMqttClientAdapter(
            $this->makeClient($host, $port, $clientId),
            $connectionSettings
        );
    }

    protected function makeClient(string $host, int $port, string $clientId): MqttClient
    {
        return new MqttClient($host, $port, $clientId);
    }

    protected function makeConnectionSettings(
        mixed $username,
        mixed $password,
        int $timeout,
        int $keepAlive
    ): ConnectionSettings {
        return (new ConnectionSettings())
            ->setUsername($username)
            ->setPassword($password)
            ->setConnectTimeout($timeout)
            ->setKeepAliveInterval($keepAlive);
    }
}
