<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ResetPasswordServices;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(ResetPasswordServices $resetPasswordServices) {
		$this->middleware('guest');
		$this->resetPasswordServices = $resetPasswordServices;
	}

	public function reset(Request $request) {
		$this->validate($request, [
			'email' => 'required|exists:users,email',
		]);

		$attributes = $request->all();

		$res = $this->resetPasswordServices->reset($attributes);

		return response($res);
	}

	public function setup(Request $request) {
		$this->validate($request, [
			'password' => 'required',
			'password_confirmation' => 'required|same:password',
			'token' => 'required|exists:password_resets,token',
		]);

		$attributes = $request->all();

		$res = $this->resetPasswordServices->setup($attributes);

		return response($res);
	}
}
