<?php

declare(strict_types=1);

namespace PlayerCentral\MqttBroker\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use PlayerCentral\MqttBroker\MqttServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MqttServiceProvider::class,
        ];
    }
}
