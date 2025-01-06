<?php

it('creates a new user', function () {
    $this->artisan('make:user')
        ->expectsQuestion("What is the new user's email address?", 'user@example.com')
        ->expectsQuestion("What is the new user's name?", 'Test User')
        ->expectsQuestion("What is the new user's password? (blank generates a random one)", '')
        ->expectsQuestion('Should the password be encrypted?', 'yes')
        ->expectsQuestion('Do you want to send a password reset email?', 'no')
        ->expectsQuestion('Do you have any custom user fields to add? Field=Value (blank continues)', '');
});

it('creates a new user with additional fields', function () {
    $this->artisan('make:user')
        ->expectsQuestion("What is the new user's email address?", 'user@example.com')
        ->expectsQuestion("What is the new user's name?", 'Test User')
        ->expectsQuestion("What is the new user's password? (blank generates a random one)", '')
        ->expectsQuestion('Should the password be encrypted?', 'yes')
        ->expectsQuestion('Do you want to send a password reset email?', 'no')
        ->expectsQuestion('Do you have any custom user fields to add? Field=Value (blank continues)', 'field=value')
        ->expectsQuestion('Do you have any custom user fields to add? Field=Value (blank continues)', '');
});
