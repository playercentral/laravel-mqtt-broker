<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Broadcasters;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;

class MqttBroadcaster extends Broadcaster
{
    public function broadcast(array $channels, $event, array $payload = [])
    {
        throw new \Exception('Not implemented');
    }

    public function auth($request): void
    {
        throw new \Exception('Not implemented');
    }

    public function validAuthenticationResponse($request, $result): void
    {
        throw new \Exception('Not implemented');
    }
}
