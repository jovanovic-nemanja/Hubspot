<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagesModules extends Model
{
    use SoftDeletes;

    protected $table   = 'pages_modules';
    protected $guarded = array('id');
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
}
