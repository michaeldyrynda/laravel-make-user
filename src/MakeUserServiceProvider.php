<?php

namespace Dyrynda\Artisan;

use Illuminate\Support\ServiceProvider;

class MakeUserServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-make-user.php' => config_path('laravel-make-user.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__'../config/laravel-make-user.php', 'laravel-make-user'
        );

        $this->app->singleton('dyrynda.artisan.make.user', function ($app) {
            return $app->make(Dyrynda\Artisan\Console\Commands\MakeUser::class);
        });

        $this->commands('dyrynda.artisan.make.user');
    }
}
