<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallMqttCommand extends Command
{
    protected $signature = 'mqtt:install';

    protected $description = 'Install and configure the MQTT Broadcasting driver';

    public function handle(): int
    {
        $this->components->info('Initializing MQTT Broker installation...');

        $this->call('vendor:publish', [
            '--provider' => "PlayerCentral\MqttBroker\MqttServiceProvider",
            '--tag' => 'mqtt-config',
        ]);

        $this->updateEnvFile();
        $this->updateBroadcastingConfig();

        $this->components->info('MQTT Broadcasting setup complete. 🚀');

        return self::SUCCESS;
    }

    protected function updateEnvFile(): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return;
        }

        $envContent = File::get($envPath);

        if (str_contains($envContent, 'MQTT_HOST')) {
            $this->components->warn('MQTT variables already exist in .env, skipping...');

            return;
        }

        $stub = "\n# MQTT Broker Configuration\n".
                "BROADCAST_CONNECTION=mqtt\n".
                "MQTT_HOST=127.0.0.1\n".
                "MQTT_PORT=1883\n".
                "MQTT_CLIENT_ID=player_central_app\n".
                "MQTT_USERNAME=\n".
                "MQTT_PASSWORD=\n".
                "MQTT_QOS=0\n".
                "MQTT_RETAIN=false\n";

        File::append($envPath, $stub);
        $this->components->task('Updating .env file with MQTT variables');
    }

    protected function updateBroadcastingConfig(): void
    {
        $path = config_path('broadcasting.php');

        if (! File::exists($path)) {
            $this->components->warn('Could not find config/broadcasting.php, skipping connection patch.');

            return;
        }

        $content = File::get($path);

        if (str_contains($content, "'mqtt' => [")) {
            $this->components->warn('MQTT broadcasting connection already exists, skipping...');

            return;
        }

        $needle = "'connections' => [";
        if (! str_contains($content, $needle)) {
            $this->components->warn('Could not find broadcasting connections array, skipping patch.');

            return;
        }

        $mqttConnection = <<<'PHP'

        'mqtt' => [
            'driver' => 'mqtt',
            'host' => env('MQTT_HOST', '127.0.0.1'),
            'port' => (int) env('MQTT_PORT', 1883),
            'client_id' => env('MQTT_CLIENT_ID', 'player_central_app'),
            'username' => env('MQTT_USERNAME'),
            'password' => env('MQTT_PASSWORD'),
            'options' => [
                'qos' => (int) env('MQTT_QOS', 0),
                'retain' => env('MQTT_RETAIN', false),
            ],
        ],
PHP;

        $patched = str_replace($needle, $needle.$mqttConnection, $content);
        File::put($path, $patched);

        $this->components->task('Updating config/broadcasting.php with MQTT connection');
    }
}
