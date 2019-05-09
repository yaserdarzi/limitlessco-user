<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class App extends Model
{
    use SoftDeletes;
    protected $table = Constants::APP_DB;
    protected $fillable = [
        'app', 'type_app', 'type_app_child'
    ];
    protected $dates = ['deleted_at'];
}
