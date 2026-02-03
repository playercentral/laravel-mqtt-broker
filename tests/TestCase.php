<?php

namespace PlayerCentral\MqttBroker\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use PlayerCentral\MqttBroker\MqttBrokerServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MqttBrokerServiceProvider::class,
        ];
    }
}
