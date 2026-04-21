<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Contracts;

interface MqttClientInterface
{
    public function connect(bool $cleanSession): void;

    public function publish(string $topic, string $message, int $qos, bool $retain): void;

    public function disconnect(): void;
}
