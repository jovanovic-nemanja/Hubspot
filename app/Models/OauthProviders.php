<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthProviders extends Model {
	protected $table = 'oauth_providers';
	protected $guarded = array('id');
	protected $dates = ['created_at', 'updated_at'];

	public function user() {
		return $this->belongsTo('\App\User', 'user_id', 'id');
	}
}
