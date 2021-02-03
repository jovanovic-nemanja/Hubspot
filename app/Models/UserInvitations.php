<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInvitations extends Model
{
    use SoftDeletes;

    protected $table   = 'user_invitations';
    protected $guarded = array('id');
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
}
