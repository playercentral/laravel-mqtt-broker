<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Tests\Fakes;

use PlayerCentral\MqttBroker\Contracts\MqttClientFactoryInterface;
use PlayerCentral\MqttBroker\Contracts\MqttClientInterface;

final class FakeMqttClientFactory implements MqttClientFactoryInterface
{
    public function __construct(public readonly FakeMqttClient $client) {}

    public function make(array $config): MqttClientInterface
    {
        return $this->client;
    }
}
