<?php

namespace App\Services;

use App\Mail\UserDeleteNotification;
use App\Mail\UserInvitation;
use App\Models\Agencies;
use App\Models\UserInvitations;
use App\User;
use Spatie\Permission\Models\Role;

class UserServices extends BaseService
{
	public function __construct()
	{
	}

	public function getAll($attributes, $user)
	{
		$user = $this->getByLoggedUser($user->id);

		$limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

		$results = $this->queryBuilder(User::class, $attributes, $include);

		if ($user['roles']['name'] != 'admin') {
			$results = $results->whereHas('agencies', function ($query) use ($user) {
				$query->where('agency_id', '=', $user['agencies']['id']);
			});
		}

		$results = $results->paginate($limit)->toArray();

		return $this->dataWrapper($results);
	}

	public function getById($id, $attributes = [])
	{
		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

		$result = User::with($include)->where('id', $id);

		return $result->firstOrFail()->toArray();
	}

	public function getByLoggedUser($id, $attributes = [])
	{
		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : ['roles', 'agencies.plans', 'agencies.billing', 'agencies.portals', 'billing'];

		$result = User::with($include)->where('id', $id);

		$user = $result->firstOrFail()->toArray();
		$user['roles'] = $user['roles'][0];
		$user['agencies'] = @$user['agencies'][0] ? $user['agencies'][0] : [];
		$user['agencies']['plans'] = @$user['agencies']['plans'][0] ? $user['agencies']['plans'][0] : [];
		$user['portals_count'] = @$user['agencies']['portals'] ? count(@$user['agencies']['portals']) : 0;

		return $user;
	}

	public function create(array $data, $user)
	{
		$data['user_id'] = $user->id;
		$data['status'] = 'inactive';

		return $this->atomic(function () use ($data) {
			$result = User::create($data);

			return $result;
		});
	}

	public function update($id, array $data)
	{
		$agency = [];
		$userData = [
			'name' => $data['name'],
			'email' => $data['email'],
		];

		if (@$data['password']) {
			$userData['password'] = bcrypt($data['password']);
		}

		$role = Role::where('name', $data['role'])->firstOrFail();

		if (@$data['agency']) {
			$agency = Agencies::where('id', $data['agency'])->firstOrFail();
		}

		return $this->atomic(function () use ($id, $userData, $role, $agency, $data) {
			$result = User::where('id', $id)->update($userData);

			$user = User::where('id', $id)->with(['agencies'])->firstOrFail();

			if (@$data['cover']) {
				Agencies::where('id', $user['agencies'][0]['id'])->update(['cover' => $data['cover']]);
			}

			$user->roles()->sync([$role['id']]);

			if (@$data['agency']) {
				$user->agencies()->sync([$agency['id']]);
			}

			return $user;
		});
	}

	public function deleteMe($user)
	{
		return $this->atomic(function () use ($user) {
			if ($user->roles[0]->name == 'user') {
				User::where('id', $user->id)->delete();
			} else if ($user->roles[0]->name == 'agency-admin') {
				User::where('id', $user->id)->delete();
			}

			$mailAttributes = [];

			\Mail::to($user->email)->send(new UserDeleteNotification($mailAttributes));
		});
	}

	public function delete($id)
	{
		User::where('id', $id)->delete();
	}

	public function invite($attributes, $user)
	{
		$inviter = User::where('id', $user->id)->with(['agencies'])->firstOrFail();
		$user = $this->getByLoggedUser($user->id);

		return $this->atomic(function () use ($attributes, $inviter, $user) {
			$token = md5($attributes['email']);

			UserInvitations::create([
				'email' => $attributes['email'],
				'role' => $attributes['role'],
				'agency_id' => ($user['roles']['name'] == 'admin') ? $attributes['agency'] : $inviter->agencies[0]->id,
				'token' => $token,
			]);

			$mailAttributes = [
				'agency_name' => ($user['roles']['name'] == 'admin') ? "Sprocket Rocket Admin" : $inviter->agencies[0]->name,
				'link' => env('APP_URL') . '/setup-account?token=' . $token,
			];

			\Mail::to($attributes['email'])->send(new UserInvitation($mailAttributes));

			return ['success' => true];
		});
	}

	public function acceptInvitation($attributes)
	{
		return $this->atomic(function () use ($attributes) {
			$invitation = UserInvitations::where('token', $attributes['token'])->firstOrFail();

			$user = User::create([
				'name' => $attributes['name'],
				'email' => $invitation['email'],
				'password' => bcrypt($attributes['password']),
			]);

			$role = Role::where('name', $invitation['role'])->firstOrFail();
			$agency = Agencies::where('id', $invitation['agency_id'])->firstOrFail();

			$user->assignRole($role);
			$agency->users()->save($user);

			UserInvitations::where('token', $attributes['token'])->delete();

			return [
				'success' => true,
				'user' => [
					'email' => $invitation['email'],
				],
			];
		});
	}

	public function search($keyword, $attributes, $user)
	{
		$user = $this->getByLoggedUser($user->id);

		$limit = (@$attributes['limit']) ? $attributes['limit'] : 10;
		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

		$results = $this->queryBuilder(User::class, $attributes, $include);

		$results = $results->where('name', 'like', '%' . $keyword . '%');

		if ($user['roles']['name'] != 'admin') {
			$results = $results->whereHas('agencies', function ($query) use ($user) {
				$query->where('agency_id', '=', $user['agencies']['id']);
			});
		}

		$results = $results->paginate($limit)->toArray();

		return $this->dataWrapper($results);
	}
}
