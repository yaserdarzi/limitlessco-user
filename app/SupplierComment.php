<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class SupplierComment extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SUPPLIER_COMMENT_DB;
    protected $fillable = [
        'supplier_id', 'name', 'comment', 'path', 'info'
    ];
}