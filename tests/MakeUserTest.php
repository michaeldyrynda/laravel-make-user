<?php

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

class MakeUserTest extends TestCase
{
    /** @test */
    public function it_creates_a_new_user()
    {
        $this->artisan('make:user')
            ->expectsQuestion("What is the new user's email address?", 'user@example.com')
            ->expectsQuestion("What is the new user's name?", 'Test User')
            ->expectsQuestion("What is the new user's password? (blank generates a random one)", '')
            ->expectsQuestion('Do you wish to force creation?', 'no')
            ->expectsQuestion('Do you want to send a password reset email?', 'no')
            ->expectsQuestion('Do you have any custom user fields to add? Field=Value (blank continues)', '');

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'name' => 'Test User',
        ]);
    }

    /** @test */
    public function it_requires_a_unique_email_address()
    {
        User::create(['name' => 'Adam Wathan', 'email' => 'adamwathan@example.com', 'password' => '']);

        $exitCode = Artisan::call('make:user', ['email' => 'adamwathan@example.com']);

        $this->assertContains('The user was not created', Artisan::output());
        $this->assertEquals(1, User::where('email', 'adamwathan@example.com')->count());
    }

    /** @test */
    public function it_hashes_the_password_when_specified()
    {
        Artisan::call('make:user', ['email' => 'michael@dyrynda.com.au', '--password' => 'secret']);

        tap(User::first(), function ($user) {
            $this->assertTrue(Hash::check('secret', $user->password));
        });
    }

    /** @test */
    public function it_sends_the_password_reset_email_when_generating_a_password()
    {
        Notification::fake();

        Artisan::call('make:user', ['email' => 'michael@dyrynda.com.au', '--name' => 'Michael Dyrynda']);

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

        Artisan::call('make:user', ['email' => 'michael@dyrynda.com.au', '--password' => 'secret']);

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

        Artisan::call('make:user', ['email' => 'michael@dyrynda.com.au', '--password' => 'secret', '--send-reset' => true]);

        Notification::assertSentTo(User::first(), ResetPassword::class);
    }

    /** @test */
    public function it_fills_additional_fields_when_specified()
    {
        Artisan::call('make:user', ['email' => 'michael@dyrynda.com.au', '--password' => 'secret', '--fields' => 'admin:true']);

        $this->assertTrue(User::where([
            'email' => 'michael@dyrynda.com.au',
            'admin' => true,
        ])->exists());
    }

    /** @test */
    public function it_handles_null_field_values_correctly()
    {
        Artisan::call('make:user', ['email' => 'michael@dyrynda.com.au', '--fields' => 'force_filled:null']);

        tap(User::first(), function ($user) {
            $this->assertNull($user->force_filled);
        });
    }

    /** @test */
    public function it_force_filles_guarded_properties_when_instructed()
    {
        Artisan::call('make:user', [
            'email' => 'michael@dyrynda.com.au',
            '--password' => 'secret',
            '--force' => true,
            '--fields' => 'admin:false,force_filled:string field',
        ]);

        tap(User::first(), function ($user) {
            $this->assertFalse($user->admin);
            $this->assertEquals('string field', $user->force_filled);
        });
    }
}
