<?php

namespace Tests;

use Tests\User;
use Tests\TestCase;
use Illuminate\Support\Manager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Auth\Passwords\ResetPasswordNotification;

class MakeUserTest extends TestCase
{
    /** @test */
    public function it_requires_a_valid_email_address()
    {
        Artisan::call('make:user', ['email' => 'invalidemail']);

        $this->assertContains('The user was not created', Artisan::output());
        $this->assertFalse(User::where('email', 'invalidemail')->exists());
    }

    /** @test */
    public function it_sends_the_password_reset_email_when_generating_a_password()
    {
        Mail::fake();

        Artisan::call('make:user', ['email' => 'michael@dyrynda.com.au', '--name' => 'Michael Dyrynda']);

        // Mail::assertSent(ResetPasswordNotification::class, function ($mail) {
        //     return $mail->hasTo('michael@dyrynda.com.au');
        // });

        $this->assertContains('Sent password reset email to', Artisan::output());
        $this->assertTrue(User::where('email', 'michael@dyrynda.com.au')->exists());
        $this->assertEquals(1, User::where('email', 'michael@dyrynda.com.au')->count());
    }
}
