<?php

namespace Dyrynda\Artisan\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Dyrynda\Artisan\Exceptions\MakeUserException;

class MakeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new application user';

    /**
     * Email address of the new user.
     *
     * @var string
     */
    protected $email;

    /**
     * Name of the new user.
     *
     * @var string
     */
    protected $name;

    /**
     * Password for the new user.
     *
     * @var string
     */
    protected $password;

    /**
     * Whether or not to send a password reset.
     *
     * @var string
     */
    protected $sendReset;

    /**
     * Execute the console command.
     *
     * Handle creation of the new application user.
     *
     * @return void
     */
    public function handle()
    {
        $this->name = $this->ask("What is the new user's name?") ?: '';
        $this->email = $this->ask("What is the new user's email address?");
        $this->password = bcrypt($this->secret("What is the new user's password? (blank generates a random one)", str_random(32)));

        if (Route::has('password.reset')) {
            $this->sendReset = $this->confirm('Do you want to send a password reset email?');
        }

        try {
            app('db')->beginTransaction();

            $this->validateEmail();

            $this->createUser();

            $this->sendReset();

            app('db')->commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());

            $this->error('The user was not created');

            app('db')->rollBack();
        }
    }

    /**
     * Create the new application user.
     *
     * @return bool
     */
    private function createUser()
    {
        $this->task('Creating user', function () {
            return app(config('auth.providers.users.model'))->create([
                'email' => $this->email,
                'name' => $this->name,
                'password' => $this->password,
            ]);
        });
    }

    /**
     * Send the password reset.
     *
     * @return void
     */
    private function sendReset()
    {
        if ($this->sendReset) {
            $this->task('Sending password reset email', function () {
                Password::sendResetLink(['email' => $this->email]);
            });
        }
    }

    /**
     * Determine if the given email address already exists.
     *
     * @return void
     *
     * @throws \Dyrynda\Artisan\Exceptions\MakeUserException
     */
    private function validateEmail()
    {
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw MakeUserException::invalidEmail($this->email);
        }

        if (app(config('auth.providers.users.model'))->where('email', $this->email)->exists()) {
            throw MakeUserException::emailExists($this->email);
        }
    }
}
