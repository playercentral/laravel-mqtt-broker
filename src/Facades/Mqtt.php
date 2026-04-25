<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Facades;

use Illuminate\Support\Facades\Facade;
use PlayerCentral\MqttBroker\Broadcasters\MqttBroadcaster;

/**
 * @method static void broadcast(array $channels, string $event, array $payload = [])
 * @method static bool auth(\Illuminate\Http\Request $request)
 * @method static mixed validAuthenticationResponse(\Illuminate\Http\Request $request, mixed $result)
 *
 * @see \PlayerCentral\MqttBroker\Broadcasters\MqttBroadcaster
 */
class Mqtt extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mqtt.broadcaster';
    }
}