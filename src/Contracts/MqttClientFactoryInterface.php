<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Contracts;

interface MqttClientFactoryInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function make(array $config): MqttClientInterface;
}
