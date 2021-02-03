<?php

namespace App\Services;

use App\Mail\ResetPassword;
use App\Models\PasswordResets;
use App\User;

class ResetPasswordServices extends BaseService
{
	public function __construct()
	{
	}

	public function reset($attributes)
	{
		return $this->atomic(function () use ($attributes) {
			$data = [
				'token' => md5($attributes['email'] . now()),
				'email' => $attributes['email'],
			];

			$mailAttributes = [
				'link' => env('APP_URL') . '/reset-password?token=' . $data['token'],
			];

			\Mail::to($attributes['email'])->send(new ResetPassword($mailAttributes));

			PasswordResets::create($data);

			return [
				'success' => true,
			];
		});
	}

	public function setup($attributes)
	{
		return $this->atomic(function () use ($attributes) {
			if (PasswordResets::where('token', $attributes['token'])->exists()) {
				$passwordToken = PasswordResets::where('token', $attributes['token'])->first()->toArray();

				User::where('email', $passwordToken['email'])->update([
					'password' => bcrypt($attributes['password']),
				]);

				PasswordResets::where('token', $attributes['token'])->delete();
			} else {
				abort(404, 'Token not found');
			}
		});
	}
}
