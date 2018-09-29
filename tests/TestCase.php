<?php

namespace Tests;

use Dyrynda\Artisan\MakeUserServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations('testing');
    }

    protected function getPackageProviders($app)
    {
        return [
            MakeUserServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('auth.providers.users.model', User::class);
        $app['router']->get('/password/reset')->name('password.reset');
    }
}
