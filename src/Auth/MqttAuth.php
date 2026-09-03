<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Auth;

class MqttAuth
{
    /**
     * Generate an HMAC-SHA256 signature for a channel authentication request.
     */
    public static function generateSignature(string $socketId, string $channel, ?string $key = null): string
    {
        $secret = $key ?? (string) config('app.key', '');

        return hash_hmac('sha256', "{$socketId}:{$channel}", $secret);
    }

    /**
     * Verify that an HMAC-SHA256 signature is valid for the given socket ID and channel.
     */
    public static function verifySignature(string $signature, string $socketId, string $channel, ?string $key = null): bool
    {
        $expected = self::generateSignature($socketId, $channel, $key);

        return hash_equals($expected, $signature);
    }

    /**
     * Generate a signed stateless token containing user ID, expiration, and optional topic wildcards.
     *
     * This is a broker-level auth primitive, distinct from generateSignature()/verifySignature()
     * (which back the Laravel Echo private-channel auth flow via MqttBroadcaster::auth()). It is
     * intended for authenticating direct MQTT client connections against a broker's HTTP auth/ACL
     * webhook (e.g. EMQX, VerneMQ), scoping a connection to the given topic wildcards for its TTL.
     * Not currently wired into any shipped auth flow — no broker webhook endpoint ships with this
     * package yet; see the "Stateless Broker Tokens" section of the README.
     *
     * @param  array<string>  $topics
     */
    public static function generateToken(int|string $userId, int $ttl = 3600, array $topics = [], ?string $key = null): string
    {
        $secret = $key ?? (string) config('app.key', '');

        $payload = json_encode([
            'sub' => $userId,
            'exp' => time() + $ttl,
            'topics' => $topics,
        ], JSON_THROW_ON_ERROR);

        $base64Payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $base64Payload, $secret);

        return "{$base64Payload}.{$signature}";
    }

    /**
     * Verify and decode a signed stateless token. Returns payload array if valid, or null if expired or invalid.
     *
     * See generateToken() for the intended broker-level auth use case.
     *
     * @return array<string, mixed>|null
     */
    public static function verifyToken(string $token, ?string $key = null): ?array
    {
        $secret = $key ?? (string) config('app.key', '');
        $parts = explode('.', $token, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$base64Payload, $signature] = $parts;

        $expectedSignature = hash_hmac('sha256', $base64Payload, $secret);
        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $remainder = strlen($base64Payload) % 4;
        if ($remainder > 0) {
            $base64Payload .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($base64Payload, '-_', '+/'), true);
        if ($decoded === false) {
            return null;
        }

        try {
            $data = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($data)) {
                return null;
            }

            if (isset($data['exp']) && (int) $data['exp'] < time()) {
                return null;
            }

            return $data;
        } catch (\Throwable) {
            return null;
        }
    }
}
