<?php

namespace Tests;

use Tests\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

class MakeUserTest extends TestCase
{
    private function makeUser($name = 'Test User', $email, $password = null, $force = 'no', $reset = 'no')
    {
        $this->artisan('make:user')
            ->expectsQuestion("What is the new user's name?", $name)
            ->expectsQuestion("What is the new user's email address?", $email)
            ->expectsQuestion("What is the new user's password? (blank generates a random one)", $password)
            ->expectsQuestion("Do you wish to force creation?", $force)
            ->expectsQuestion("Do you want to send a password reset email?", $reset);

        dump(User::all()->toArray());
    }

    /** @test */
    public function it_creates_a_new_user()
    {
        $this->makeUser('Test User', 'user@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'name' => 'Test User',
        ]);
    }

    /** @test */
    public function it_requires_a_unique_email_address()
    {
        User::create(['name' => 'Adam Wathan', 'email' => 'adamwathan@example.com', 'password' => '']);

        $exitCode = $this->makeUser('Adam Wathan', 'adamwathan@example.com');

        $this->assertContains('The user was not created', Artisan::output());
        $this->assertEquals(1, User::where('email', 'adamwathan@example.com')->count());
    }

    /** @test */
    public function it_hashes_the_password_when_specified()
    {
        $this->makeUser('Michael Dyrynda', 'michael@dyrynda.com.au', 'secret');

        tap(User::first(), function ($user) {
            $this->assertTrue(Hash::check('secret', $user->password));
        });
    }

    /** @test */
    public function it_sends_the_password_reset_email_when_generating_a_password()
    {
        Notification::fake();

        $this->artisan('make:user', ['email' => 'michael@dyrynda.com.au', '--name' => 'Michael Dyrynda']);

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

        $this->artisan('make:user', ['email' => 'michael@dyrynda.com.au', '--password' => 'secret']);

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

        $this->artisan('make:user', ['email' => 'michael@dyrynda.com.au', '--password' => 'secret', '--send-reset' => true]);

        Notification::assertSentTo(User::first(), ResetPassword::class);
    }
}
