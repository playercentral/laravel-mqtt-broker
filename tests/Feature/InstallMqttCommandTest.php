<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('installs mqtt defaults and avoids duplicate patches', function () {
    /** @var \PlayerCentral\MqttBroker\Tests\TestCase $this */
    $envPath = base_path('.env');
    $configPath = config_path('broadcasting.php');

    File::put($envPath, "APP_NAME=Laravel\n");
    File::ensureDirectoryExists(dirname($configPath));
    File::put($configPath, <<<'PHP'
<?php

return [
    'default' => env('BROADCAST_CONNECTION', 'null'),
    'connections' => [
    ],
];
PHP);

    $this->artisan('mqtt:install')->assertExitCode(0);
    $this->artisan('mqtt:install')->assertExitCode(0);

    $envContent = File::get($envPath);
    $configContent = File::get($configPath);

    expect(substr_count($envContent, 'MQTT_HOST'))->toBe(1);
    expect($envContent)->toContain('BROADCAST_CONNECTION=mqtt');
    expect(substr_count($configContent, "'mqtt' => ["))->toBe(1);
    expect($configContent)->toContain("'driver' => 'mqtt'");
});
