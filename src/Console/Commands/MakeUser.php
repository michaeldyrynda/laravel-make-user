<?php

namespace Dyrynda\Artisan\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Dyrynda\Artisan\Exceptions\MakeUserException;

class MakeUser extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user {--p|plaintext}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new application user';

    /**
     * Array of custom fields to attach to the user.
     *
     * @var array
     */
    protected $customFields = [];

    /**
     * Execute the console command.
     *
     * Handle creation of the new application user.
     *
     * @return void
     */
    public function handle() {
        $email = $this->ask("What is the new user's email address?");
        $name = $this->ask("What is the new user's name?") ?: '';
        $password = $this->secret("What is the new user's password? (blank generates a random one)", str_random(32));
        $sendReset = $this->confirm('Do you want to send a password reset email?');

        if (!$this->option('plaintext')) {
            bcrypt($password);
        }

        while ($custom = $this->ask('Do you have any custom user fields to add? Field=Value (blank continues)', false)) {
            list($key, $value) = explode('=', $custom);
            $this->customFields[$key] = value($value);
        }

        try {
            app('db')->beginTransaction();

            $this->validateEmail($email);

            app(config('auth.providers.users.model'))->create(array_merge(
                compact('email', 'name', 'password'),
                $this->customFields
            ));

            if ($sendReset) {
                Password::sendResetLink(compact('email'));

                $this->info("Sent password reset email to {$email}");
            }

            $this->info("Created new user for email {$email}");

            app('db')->commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());

            $this->error('The user was not created');

            app('db')->rollBack();
        }
    }

    /**
     * Determine if the given email address already exists.
     *
     * @param  string  $email
     * @return void
     *
     * @throws \Dyrynda\Artisan\Exceptions\MakeUserException
     */
    private function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw MakeUserException::invalidEmail($email);
        }

        if (app(config('auth.providers.users.model'))->where('email', $email)->exists()) {
            throw MakeUserException::emailExists($email);
        }
    }
}
