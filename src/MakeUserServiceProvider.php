<?php

namespace Dyrynda\Artisan;

use Illuminate\Support\ServiceProvider;
use Dyrynda\Artisan\Console\Commands\MakeUser;

class MakeUserServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('dyrynda.artisan.make:user', function ($app) {
            return $app->make(MakeUser::class);
        });

        $this->commands('dyrynda.artisan.make:user');
    }
}
