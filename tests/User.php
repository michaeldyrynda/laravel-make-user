<?php

namespace Tests;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'admin'
    ];

    protected $hidden = [
        'password', 'api_token', 'remember_token',
    ];

    protected $casts = ['admin' => 'bool'];
}
