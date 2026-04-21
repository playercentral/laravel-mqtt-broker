<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Mqtt;

use PhpMqtt\Client\MqttClient;
use PlayerCentral\MqttBroker\Contracts\MqttClientInterface;

class PhpMqttClientAdapter implements MqttClientInterface
{
    public function __construct(
        private readonly MqttClient $client,
        private readonly \PhpMqtt\Client\ConnectionSettings $connectionSettings
    ) {}

    public function connect(bool $cleanSession): void
    {
        $this->client->connect($this->connectionSettings, $cleanSession);
    }

    public function publish(string $topic, string $message, int $qos, bool $retain): void
    {
        $this->client->publish($topic, $message, $qos, $retain);
    }

    public function disconnect(): void
    {
        $this->client->disconnect();
    }
}
