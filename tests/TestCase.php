<?php

namespace Tests;

use Tests\User;
use Dyrynda\Artisan\MakeUserServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    protected function getPackageProviders($app)
    {
        return [
            MakeUserServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $app['config']->set('auth.providers.users.model', User::class);
    }
}
