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
            __DIR__.'/config/broadcasting.php' => config_path('broadcasting.php'),
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
        $packageConfig = require __DIR__.'/config/broadcasting.php';
        $config = $this->app->make('config');
        $config->set(
            'broadcasting',
            array_replace_recursive($packageConfig, $config->get('broadcasting', []))
        );

        $this->app->singleton(MqttClientFactoryInterface::class, PhpMqttClientFactory::class);

        // Register the default MQTT broadcaster instance
        $this->app->singleton('mqtt.broadcaster', function ($app) {
            $config = (array) config('broadcasting.connections.mqtt', []);

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
