<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalOauthProviders extends Model
{
    protected $table = 'portals_oauth_extended';
    protected $guarded = array('id');
    protected $dates = ['created_at', 'updated_at'];
}
