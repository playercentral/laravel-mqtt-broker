<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;
use PlayerCentral\MqttBroker\Broadcasters\MqttBroadcaster;
use PlayerCentral\MqttBroker\Console\Commands\InstallMqttCommand;
use PlayerCentral\MqttBroker\Contracts\MqttClientFactoryInterface;
use PlayerCentral\MqttBroker\Mqtt\PhpMqttClientFactory;
use Illuminate\Support\Facades\Broadcast;

class MqttServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/mqtt.php' => config_path('mqtt.php'),
        ], 'mqtt-config');

        Broadcast::extend('mqtt', function ($app, array $config) {
            return new MqttBroadcaster(
                $config,
                $app->make(MqttClientFactoryInterface::class)
            );
        });

        // $this->app->resolving(BroadcastManager::class, function (BroadcastManager $manager): void {
        //     $manager->extend('mqtt', function ($app, array $config): MqttBroadcaster {
        //         return new MqttBroadcaster(
        //             $config,
        //             $app->make(MqttClientFactoryInterface::class)
        //         );
        //     });
        // });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/mqtt.php', 'mqtt');
        $this->app->singleton(MqttClientFactoryInterface::class, PhpMqttClientFactory::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallMqttCommand::class,
            ]);
        }
    }
}
