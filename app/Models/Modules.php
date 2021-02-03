<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    protected $table = 'modules';
    protected $guarded = array('id');
    protected $dates = ['created_at', 'updated_at'];

    public function getModulesAttribute($value)
    {
        return json_decode($value, true);
    }
}
