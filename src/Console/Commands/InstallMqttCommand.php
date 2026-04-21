<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'mqtt:install';
    protected $description = 'Install and configure the MQTT Broadcasting driver';

    public function handle(): int
    {
        $this->components->info('Initializing MQTT Broker installation...');

        $this->call('vendor:publish', [
            '--provider' => "PlayerCentral\MqttBroker\MqttServiceProvider",
            '--tag' => "mqtt-config"
        ]);

        $this->updateEnvFile();

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

        $stub = "\n# MQTT Broker Configuration\n" .
                "BROADCAST_CONNECTION=mqtt\n" .
                "MQTT_HOST=127.0.0.1\n" .
                "MQTT_PORT=1883\n" .
                "MQTT_CLIENT_ID=player_central_app\n";

        File::append($envPath, $stub);
        $this->components->task('Updating .env file with MQTT variables');
    }
}