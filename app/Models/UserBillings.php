<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBillings extends Model
{
    use SoftDeletes;

    protected $table   = 'user_billings';
    protected $guarded = array('id');
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
}
