<?php

namespace Tests;

use Dyrynda\Artisan\MakeUserServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            MakeUserServiceProvider::class,
        ];
    }
}
