<?php

namespace Dyrynda\Artisan\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Dyrynda\Artisan\Exceptions\MakeUserException;

class MakeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user {email} {--name=      : Set the name for the new user}
                                              {--password=  : The password to set for the new user}
                                              {--send-reset : Send a password reset email for the new user}
                                              {--fields=    : Additional database fields to set on the user}
                                              {--force      : Create the user model circumventing guarded fields}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new application user';

    /**
     * Execute the console command.
     *
     * Handle creation of the new application user.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?: '';
        $password = bcrypt($this->option('password') ?: str_random(32));
        $modelCommand = $this->option('force') ? 'forceCreate' : 'create';
        $sendReset = ! $this->option('password') || $this->option('send-reset');

        try {
            app('db')->beginTransaction();

            $this->validateEmail($email);

            app(config('auth.providers.users.model'))->{$modelCommand}(array_merge(
                compact('email', 'name', 'password'),
                $this->additionalFields()
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
    private function validateEmail($email)
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw MakeUserException::invalidEmail($email);
        }

        if (app(config('auth.providers.users.model'))->where('email', $email)->exists()) {
            throw MakeUserException::emailExists($email);
        }
    }

    /**
     * Return any additional database fields passed by the --fields option.
     *
     * @return array
     */
    private function additionalFields()
    {
        if (! $this->option('fields')) {
            return [];
        }

        return collect(explode(',', $this->option('fields')))->mapWithKeys(function ($field) {
            list($column, $value) = explode(':', $field);

            return [trim($column) => $this->normaliseValue($value)];
        })->toArray();
    }

    /**
     * Normalise the given (database) field input value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    private function normaliseValue($value)
    {
        if ($value == 'null') {
            return null;
        }

        if (in_array($value, [1, 'true', true], true)) {
            return true;
        }

        if (in_array($value, [0, 'false', false], true)) {
            return false;
        }

        return trim($value);
    }
}
