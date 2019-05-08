<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
    protected $table = Constants::PASSWORD_RESETS_DB;
    protected $fillable = [
        'email', 'token', 'created_at',
    ];
    public $timestamps = false;
}
