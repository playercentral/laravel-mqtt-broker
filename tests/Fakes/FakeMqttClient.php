<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Tests\Fakes;

use PlayerCentral\MqttBroker\Contracts\MqttClientInterface;

final class FakeMqttClient implements MqttClientInterface
{
    public bool $connected = false;

    public bool $disconnected = false;

    public bool $cleanSession = true;

    /** @var array<int, array{topic: string, message: string, qos: int, retain: bool}> */
    public array $published = [];

    public function connect(bool $cleanSession): void
    {
        $this->connected = true;
        $this->cleanSession = $cleanSession;
    }

    public function publish(string $topic, string $message, int $qos, bool $retain): void
    {
        $this->published[] = [
            'topic' => $topic,
            'message' => $message,
            'qos' => $qos,
            'retain' => $retain,
        ];
    }

    public function disconnect(): void
    {
        $this->disconnected = true;
    }
}
