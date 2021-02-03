<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plans extends Model {
	use SoftDeletes;

	protected $table = 'plans';
	protected $guarded = array('id');
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
	protected $appends = array('currency_symbol');

	public function agencies() {
		return $this->belongsToMany('\App\Models\Agencies', 'agencies_plans', 'plan_id', 'agency_id');
	}

	public function getCurrencySymbolAttribute() {
		if ($this->currency === 'USD' || $this->currency === 'usd') {
			return '$';
		} else if ($this->currency === 'AUD' || $this->currency === 'aud') {
			return 'A$';
		} else if ($this->currency === 'CAD' || $this->currency === 'cad') {
			return 'C$';
		} else if ($this->currency === 'EURO' || $this->currency === 'euro') {
			return '€';
		} else if ($this->currency === 'GBP' || $this->currency === 'gbp') {
			return '£';
		}
	}
}
