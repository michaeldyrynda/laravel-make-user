<?php
namespace Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

class MakeUserTest extends TestCase
{
    /** @test */
    public function it_requires_a_valid_email_address()
    {
        Artisan::call('make:user', ['--email' => 'invalidemail']);

        $this->assertFalse(User::where('email', 'invalidemail')->exists());
    }

    /** @test */
    public function it_requires_a_unique_email_address()
    {
        User::create(['name' => 'Adam Wathan', 'email' => 'adamwathan@example.com', 'password' => '']);

        $exitCode = Artisan::call('make:user', ['--email' => 'adamwathan@example.com']);

        $this->assertFileNotCreatedMsg();
        $this->assertEquals(1, User::where('email', 'adamwathan@example.com')->count());
    }

    /** @test */
    public function it_hashes_the_password_when_specified()
    {
        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--password' => 'secret']);

        tap(User::first(), function ($user) {
            $this->assertTrue(Hash::check('secret', $user->password));
        });
    }

    /** @test */
    public function it_sends_the_password_reset_email_when_generating_a_password()
    {
        Notification::fake();

        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--name' => 'Michael Dyrynda']);

        Notification::assertSentTo(User::first(), ResetPassword::class);

        tap(['email' => 'michael@dyrynda.com.au', 'name' => 'Michael Dyrynda'], function ($credentials) {
            $this->assertTrue(User::where($credentials)->exists());
            $this->assertEquals(1, User::where($credentials)->count());
        });
    }

    /** @test */
    public function it_does_not_send_the_password_reset_email_when_the_password_is_specified()
    {
        Notification::fake();

        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--password' => 'secret']);

        Notification::assertNotSentTo(User::first(), ResetPassword::class);

        tap(['email' => 'michael@dyrynda.com.au'], function ($credentials) {
            $this->assertTrue(User::where($credentials)->exists());
            $this->assertEquals(1, User::where($credentials)->count());
        });
    }

    /** @test */
    public function it_sends_the_password_reset_email_when_flagged_to_do_so()
    {
        Notification::fake();

        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--password' => 'secret', '--send-reset' => true]);

        Notification::assertSentTo(User::first(), ResetPassword::class);
    }

    /** @test */
    public function it_fills_additional_fields_when_specified()
    {
        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--password' => 'secret', '--fields' => 'admin:true']);

        $this->assertTrue(User::where([
            'email' => 'michael@dyrynda.com.au',
            'admin' => true,
        ])->exists());
    }

    /** @test */
    public function it_handles_null_field_values_correctly()
    {
        Artisan::call('make:user', ['--email' => 'michael@dyrynda.com.au', '--fields' => 'force_filled:null']);

        tap(User::first(), function ($user) {
            $this->assertNull($user->force_filled);
        });
    }

    /** @test */
    public function it_force_filles_guarded_properties_when_instructed()
    {
        Artisan::call('make:user', [
            '--email' => 'michael@dyrynda.com.au',
            '--password' => 'secret',
            '--force' => true,
            '--fields' => 'admin:false,force_filled:string field',
        ]);

        tap(User::first(), function ($user) {
            $this->assertFalse($user->admin);
            $this->assertEquals('string field', $user->force_filled);
        });
    }

    //----------------------------------------------------------------------------------
    // BULK IMPORT TESTS
    //----------------------------------------------------------------------------------

    /** @test */
    public function it_requires_a_file_with_extension()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/file-without-ext',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_real_file_on_disk()
    {
        Artisan::call('make:user', [
            '--import-file' => 'non-existent-file.csv',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_file_of_supported_type()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/file.invalid-extension',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_non_empty_file()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/empty.csv',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_well_formed_csv_file()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/invalid.csv',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_a_well_formed_json_file()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/invalid.json',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_valid_email_addresses_in_json()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/bad-email.json',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_requires_valid_email_addresses_in_csv()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/bad-email.csv',
        ]);

        $this->assertFileNotCreatedMsg();
    }

    /** @test */
    public function it_imports_csv_and_hashes_the_password()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/valid.csv',
        ]);

        $user1 = User::where([
            'name' => 'jon',
            'email' => 'jon@email.com',
        ])->first();

        $user2 = User::where([
            'name' => 'jane',
            'email' => 'jane@email.com',
        ])->first();

        $this->assertNotNull($user1);
        $this->assertTrue(Hash::check('pass123', $user1->password));
        $this->assertNotNull($user2);
        $this->assertTrue(Hash::check('pass456', $user2->password));
    }

    /** @test */
    public function it_imports_csv_that_excludes_password_but_creates_default_one()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/no-password.csv',
        ]);

        $user1 = User::where([
            'name' => 'jon',
            'email' => 'jon@email.com',
        ])->first();

        $user2 = User::where([
            'name' => 'jane',
            'email' => 'jane@email.com',
        ])->first();

        $this->assertNotNull($user1);
        $this->assertNotNull($user1->password);
        $this->assertNotNull($user2);
        $this->assertNotNull($user2->password);
    }

    /** @test */
    public function it_imports_json_and_hashes_the_password()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/valid.json',
        ]);

        $user1 = User::where([
            'name' => 'jon',
            'email' => 'jon@email.com',
        ])->first();

        $user2 = User::where([
            'name' => 'jane',
            'email' => 'jane@email.com',
        ])->first();

        $this->assertNotNull($user1);
        $this->assertTrue(Hash::check('pass123', $user1->password));
        $this->assertNotNull($user2);
        $this->assertTrue(Hash::check('pass456', $user2->password));
    }

    /** @test */
    public function it_imports_json_that_excludes_password_but_creates_default_one()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/no-password.json',
        ]);

        $user1 = User::where([
            'name' => 'jon',
            'email' => 'jon@email.com',
        ])->first();

        $user2 = User::where([
            'name' => 'jane',
            'email' => 'jane@email.com',
        ])->first();

        $this->assertNotNull($user1);
        $this->assertNotNull($user1->password);
        $this->assertNotNull($user2);
        $this->assertNotNull($user2->password);
    }

    /** @test */
    public function it_imports_file_and_fills_additional_fields_when_specified()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/valid.json',
        ]);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
            $this->assertEquals($user->admin, true);
            $this->assertNull($user->force_filled);
        });
    }

    /** @test */
    public function it_imports_file_and_ignores_guarded_properties()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/valid.json',
        ]);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
            $this->assertNull($user->force_filled);
        });
    }

    /** @test */
    public function it_imports_file_and_force_fills_guarded_properties_when_instructed()
    {
        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/valid.csv',
            '--force' => true,
        ]);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
            $this->assertEquals('yes', $user->force_filled);
        });
    }

    /** @test */
    public function it_imports_file_and_sends_the_password_reset_email_when_generating_a_password()
    {
        Notification::fake();

        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/no-password.json',
        ]);

        Notification::assertSentTo(User::first(), ResetPassword::class);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
        });
    }

    /** @test */
    public function it_imports_file_and_does_not_send_password_reset_email_when_supplied_with_password()
    {
        Notification::fake();

        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/valid.json',
        ]);

        Notification::assertNotSentTo(User::first(), ResetPassword::class);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
        });
    }

    /** @test */
    public function it_imports_file_and_sends_the_password_reset_email_when_flagged_to_do_so()
    {
        Notification::fake();

        Artisan::call('make:user', [
            '--import-file' => __DIR__.'/data_files/valid.json',
            '--send-reset' => true,
        ]);

        Notification::assertSentTo(User::first(), ResetPassword::class);

        tap(User::first(), function ($user) {
            $this->assertNotNull($user);
            $this->assertEquals($user->name, 'jon');
            $this->assertEquals($user->email, 'jon@email.com');
        });
    }

    private function assertFileNotCreatedMsg()
    {
        $this->assertContains('not created', Artisan::output());
    }
}
