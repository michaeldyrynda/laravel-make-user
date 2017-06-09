<?php

namespace Tests;

use Tests\User;
use Tests\TestCase;
use Illuminate\Support\Manager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

class MakeUserTest extends TestCase
{
    /** @test */
    public function it_requires_a_valid_email_address()
    {
        Artisan::call('make:user', ['email' => 'invalidemail']);

        $this->assertFalse(User::where('email', 'invalidemail')->exists());
    }

    /** @test */
    public function it_sends_the_password_reset_email_when_generating_a_password()
    {
        Notification::fake();

        Artisan::call('make:user', ['email' => 'michael@dyrynda.com.au', '--name' => 'Michael Dyrynda']);

        Notification::assertSentTo(User::first(), ResetPassword::class);

        $this->assertTrue(User::where($credentials = [
            'email' => 'michael@dyrynda.com.au',
            'name' => 'Michael Dyrynda',
        ])->exists());
        $this->assertEquals(1, User::where($credentials)->count());
    }
}
