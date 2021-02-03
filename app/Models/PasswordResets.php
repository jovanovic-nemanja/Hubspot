<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model {

	protected $table = 'password_resets';
	protected $dates = ['created_at'];

	public $timestamps = false;

	protected $fillable = [
		'token', 'email',
	];
}
