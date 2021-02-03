<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use SoftDeletes;

    protected $table   = 'histories';
    protected $guarded = array('id');
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo('\App\User', 'user_id', 'id');
    }

    public function agency()
    {
        return $this->belongsTo('\App\Models\Agencies', 'agency_id', 'id');
    }
}
