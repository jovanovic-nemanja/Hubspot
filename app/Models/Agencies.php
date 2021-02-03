<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Agencies extends Model
{
    use SoftDeletes, Billable;

    protected $table   = 'agencies';
    protected $guarded = array('id');
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $appends = array('portals_count', 'users_count');

    public function users()
    {
        return $this->belongsToMany('\App\User', 'agencies_users', 'agency_id', 'user_id');
    }

    public function plans()
    {
        return $this->belongsToMany('\App\Models\Plans', 'agencies_plans', 'agency_id', 'plan_id');
    }

    public function portals()
    {
        return $this->hasMany('\App\Models\Portals', 'agency_id', 'id');
    }

    public function pages()
    {
        return $this->hasMany('\App\Models\Pages', 'agency_id', 'id');
    }

    public function billing()
    {
        return $this->hasOne('\App\Models\AgencyBillings', 'agency_id', 'id');
    }

    public function getPortalsCountAttribute()
    {
        return Portals::whereHas('agency', function ($query) {
            $query->where('agency_id', '=', $this->id);
        })->count();
    }

    public function getUsersCountAttribute()
    {
        return User::whereHas('agencies', function ($query) {
            $query->where('agency_id', '=', $this->id);
        })->count();
    }
}
