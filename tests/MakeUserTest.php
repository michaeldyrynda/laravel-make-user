<?php

namespace Tests;

class MakeUserTest extends TestCase
{
    /** @test */
    public function it_creates_a_new_user()
    {
        $this->artisan('make:user')
            ->expectsQuestion("What is the new user's email address?", 'user@example.com')
            ->expectsQuestion("What is the new user's name?", 'Test User')
            ->expectsQuestion("What is the new user's password? (blank generates a random one)", '')
            ->expectsQuestion('Should the password be encrypted?', 'yes')
            ->expectsQuestion('Do you want to send a password reset email?', 'no')
            ->expectsQuestion('Do you have any custom user fields to add? Field=Value (blank continues)', '');
    }

    /** @test */
    public function it_creates_a_new_user_with_additional_fields()
    {
        $this->artisan('make:user')
            ->expectsQuestion("What is the new user's email address?", 'user@example.com')
            ->expectsQuestion("What is the new user's name?", 'Test User')
            ->expectsQuestion("What is the new user's password? (blank generates a random one)", '')
            ->expectsQuestion('Should the password be encrypted?', 'yes')
            ->expectsQuestion('Do you want to send a password reset email?', 'no')
            ->expectsQuestion('Do you have any custom user fields to add? Field=Value (blank continues)', 'field=value')
            ->expectsQuestion('Do you have any custom user fields to add? Field=Value (blank continues)', '');
    }
}
