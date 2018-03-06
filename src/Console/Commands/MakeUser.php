<?php

namespace Dyrynda\Artisan\Console\Commands;

use Exception;
use SplFileInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Dyrynda\Artisan\Exceptions\MakeUserException;
use Dyrynda\Artisan\Exceptions\ImportFileException;
use Dyrynda\Artisan\BulkImport\BulkImportFileHandler;

class MakeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user
                                {--email=       : Set the email for the new user}
                                {--name=        : Set the name for the new user}
                                {--password=    : The password to set for the new user}
                                {--send-reset   : Send a password reset email for the new user}
                                {--fields=      : Additional database fields to set on the user}
                                {--force        : Create the user model circumventing guarded fields}
                                {--import-file= : Relative path and filename for a file to import users from. File name MUST contain the extension representing the type of file (Ex: ./path/to/file.csv)}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new application users';

    /**
     * Execute the console command.
     *
     * Handle creation of the new application user.
     *
     * @return void
     */
    public function handle()
    {
        $dataToProcess = [];

        try {
            $bulkImportFile = is_string($this->option('import-file')) ? $this->fileHandlerFactory($this->option('import-file')) : null;

            $modelCommand = $this->option('force') ? 'forceCreate' : 'create';

            if (! is_null($bulkImportFile)) {
                $dataToProcess = $bulkImportFile->getData();

                $sendReset = false;

                if (! in_array('password', array_keys($dataToProcess[0])) || $this->option('send-reset')) {
                    $sendReset = true;
                }
                $dataToProcess = $this->setPasswords($dataToProcess);

            } else {
                $email = $this->option('email');
                $name = $this->option('name') ?: '';
                $password = bcrypt($this->option('password') ?: str_random(32));
                $sendReset = ! $this->option('password') || $this->option('send-reset');

                $dataToProcess[0] = [
                    'email' => $email,
                    'name' => $name,
                    'password' => $password,
                ];
            }

            foreach ($dataToProcess as $dataRow) {

                $email = $dataRow['email'] ?? null;

                app('db')->beginTransaction();

                $this->validateEmail($email);

                app(config('auth.providers.users.model'))->{$modelCommand}(array_merge(
                    $dataRow,
                    $bulkImportFile ? [] : $this->additionalFields()
                ));

                if ($sendReset) {
                    Password::sendResetLink(compact('email'));
                }

                app('db')->commit();
            }

            if (count($dataToProcess)) {
                $createdMessage = $bulkImportFile
                    ? "Created " . count($dataToProcess) . " user(s)."
                    : "Created new user for email {$email}.";

                $passwordResetMessage =  $bulkImportFile
                    ? "Sent password reset emails."
                    : "Sent password reset email to {$email}.";

                $this->info($createdMessage);

                if ($sendReset) {
                    $this->info($passwordResetMessage);
                }
                
            } else {
                $this->error('The user(s) were not created');
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());

            $this->error('The user(s) were not created');

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
            return;
        }

        if (in_array($value, [1, 'true', true], true)) {
            return true;
        }

        if (in_array($value, [0, 'false', false], true)) {
            return false;
        }

        return trim($value);
    }

    /**
     * Create file handler objects
     *
     * @param string  $path
     * @return BulkImportFileHandler
     *
     * @throws \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    private function fileHandlerFactory($path) : BulkImportFileHandler
    {
        if (! strpos($path, '.')) {
            throw ImportFileException::noExtension();
        }

        $file = new SplFileInfo($path);

        if (! class_exists($class = '\\Dyrynda\\Artisan\\BulkImport\\Handlers\\' . studly_case($file->getExtension()))) {
            throw ImportFileException::unsupported($file->getExtension());
        }

        return new $class($path);
    }

    /**
     * Add default password to data
     *
     * @param array  $data
     * @return array
     *
     */
    private function setPasswords($data)
    {
        return collect($data)->map(function($row){
            return array_merge(
                $row,
                ! isset($row['password']) ? ['password' => str_random(32)] : ['password' => bcrypt($row['password'])]
            );
        })->all();
    }
}
