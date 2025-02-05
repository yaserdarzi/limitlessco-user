<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'phone', 'email', 'gmail', 'username', 'name', 'tell',
        'image', 'gender', 'ref_link', 'info',
        'password', 'password_username', 'remember_token'
    ];
    protected $dates = ['deleted_at'];
    protected $hidden = [
        'password', 'password_username', 'remember_token',
    ];
}
