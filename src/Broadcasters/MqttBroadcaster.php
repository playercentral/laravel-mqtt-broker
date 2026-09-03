<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Broadcasters;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PlayerCentral\MqttBroker\Auth\MqttAuth;
use PlayerCentral\MqttBroker\Contracts\MqttClientFactoryInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class MqttBroadcaster extends Broadcaster
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly MqttClientFactoryInterface $clientFactory
    ) {}

    public function broadcast(array $channels, $event, array $payload = [])
    {
        if ($channels === []) {
            return;
        }

        $qos = (int) Arr::get($this->config, 'options.qos', 0);
        $cleanSession = $this->toBool(Arr::get($this->config, 'options.clean_session', true), true);
        $retain = $this->toBool(Arr::get($this->config, 'options.retain', false), false);
        $prefix = trim((string) Arr::get($this->config, 'topic_prefix', ''), '/');
        $client = $this->clientFactory->make($this->config);

        if ($qos < 0 || $qos > 2) {
            throw new RuntimeException('Invalid MQTT QoS value. Allowed values are 0, 1, or 2.');
        }

        try {
            $client->connect($cleanSession);

            foreach ($channels as $channel) {
                $topic = $this->buildTopic((string) $channel, $prefix);

                $message = json_encode([
                    'event' => $event,
                    'payload' => $payload,
                ], JSON_THROW_ON_ERROR);

                $client->publish($topic, $message, $qos, $retain);
            }
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to broadcast MQTT message.', 0, $exception);
        } finally {
            try {
                $client->disconnect();
            } catch (Throwable) {
                // noop: disconnect may fail if connect never succeeded.
            }
        }
    }

    public function auth($request)
    {
        if (! $request instanceof Request) {
            throw new RuntimeException('Invalid authentication request for MQTT broadcaster.');
        }

        $rawChannelName = (string) $request->input('channel_name', '');

        if (str_starts_with($rawChannelName, 'presence-')) {
            throw new RuntimeException('Presence channels are not currently supported by the MQTT broadcaster.');
        }

        if (! $this->isGuardedChannel($rawChannelName)) {
            return ['authenticated' => true];
        }

        $channelName = $this->normalizeChannelName($rawChannelName);

        if ($rawChannelName === '' || ! $this->retrieveUser($request, $channelName)) {
            throw new AccessDeniedHttpException();
        }

        return parent::verifyUserCanAccessChannel($request, $channelName);
    }

    public function validAuthenticationResponse($request, $result)
    {
        $rawChannelName = (string) $request->input('channel_name', '');
        $socketId = (string) $request->input('socket_id', '');
        $key = (string) Arr::get($this->config, 'key', config('app.key', ''));

        $signature = MqttAuth::generateSignature($socketId, $rawChannelName, $key);

        return [
            'auth' => $signature,
            'channel' => $rawChannelName,
        ];
    }

    protected function normalizeChannelName(string $channel): string
    {
        if (str_starts_with($channel, 'private-')) {
            return substr($channel, 8);
        }

        if (str_starts_with($channel, 'presence-')) {
            return substr($channel, 9);
        }

        return $channel;
    }

    protected function isGuardedChannel(string $channel): bool
    {
        return str_starts_with($channel, 'private-') || str_starts_with($channel, 'presence-');
    }

    private function toBool(mixed $value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return $default;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized ?? $default;
    }

    private function buildTopic(string $channel, string $prefix): string
    {
        $topic = trim($channel, '/');

        return $prefix !== '' ? "{$prefix}/{$topic}" : $topic;
    }
}
