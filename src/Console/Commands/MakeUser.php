<?php

namespace Dyrynda\Artisan\Console\Commands;

use Illuminate\Console\Command;

class MakeUser extends Command
{
    protected $signature = 'make:user {email} {--name=      : Set the name for the new user}
                                              {--password=  : The password to set for the new user}
                                              {--send-reset : Send a password reset email for the new user}';

    public function handle()
    {
        //
    }
}
