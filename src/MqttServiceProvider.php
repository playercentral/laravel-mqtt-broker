<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;
use PlayerCentral\MqttBroker\Broadcasters\MqttBroadcaster;
use PlayerCentral\MqttBroker\Console\Commands\InstallMqttCommand;
use PlayerCentral\MqttBroker\Contracts\MqttClientFactoryInterface;
use PlayerCentral\MqttBroker\Mqtt\PhpMqttClientFactory;

class MqttServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/mqtt.php' => config_path('mqtt.php'),
            __DIR__ . '/config/broadcast.php' => config_path('broadcast.php'),
        ], 'mqtt-config');

        $this->app->resolving(BroadcastManager::class, function (BroadcastManager $manager): void {
            $manager->extend('mqtt', function ($app, array $config): MqttBroadcaster {
                return new MqttBroadcaster(
                    $config,
                    $app->make(MqttClientFactoryInterface::class)
                );
            });
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/mqtt.php', 'mqtt');
        $this->mergeConfigFrom(__DIR__ . '/config/broadcast.php', 'broadcast');
        $this->app->singleton(MqttClientFactoryInterface::class, PhpMqttClientFactory::class);
        
        // Register the MQTT broadcaster instance
        $this->app->singleton('mqtt.broadcaster', function ($app) {
            $config = config('broadcast.connections.mqtt');
            return new MqttBroadcaster(
                $config,
                $app->make(MqttClientFactoryInterface::class)
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallMqttCommand::class,
            ]);
        }
    }
}
