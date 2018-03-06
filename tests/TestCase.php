<?php

namespace Tests;

use Dyrynda\Artisan\MakeUserServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->destroyFiles();

        $this->loadLaravelMigrations('testing');

        $this->loadMigrationsFrom(realpath(__DIR__.'/migrations'));
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
        $app['router']->get('/password/reset')->name('password.reset');
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->destroyFiles();
    }

    private function destroyFiles()
    {
        @unlink($this->getFilePath('csv'));
        @unlink($this->getFilePath('json'));
    }

    public function getFilePath($ext)
    {
        return realpath(realpath(__DIR__) . '/../storage/framework/testing').'/import.'.$ext;
    }
}
