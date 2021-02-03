<?php

namespace App\Services;

use App\Models\Plans;
use App\Services\UserServices;

class PlanServices extends BaseService
{
	protected $userServices;

	public function __construct(UserServices $userServices)
	{
		$this->userServices = $userServices;
	}

	public function getAll($attributes, $user)
	{
		$user = $this->userServices->getByLoggedUser($user->id);

		$limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

		$results = $this->queryBuilder(Plans::class, $attributes, $include);

		if ($user['roles']['name'] != 'admin') {
		    if ($user['agencies']['agency_plan']) {
		        $results = $results->where(function($query){
                    $query->where('agency_plan', 1)
                          ->orWhere('type', 'free');
                });
            } else {
                if ($user['agencies']['plans']['type'] == 'fixed') {
                    $results = $results->where(function($query){
                        $query->where('agency_plan', 0)
                              ->orWhere('type', '<>', 'paid');
                    });
                } else {
                    $results = $results->where(function($query){
                        $query->where('agency_plan', 0);
                    });
                }
            }
		}

		$results = $results->paginate($limit)->toArray();

		if ($user['roles']['name'] != 'admin') {
			foreach ($results['data'] as $key => $result) {
				if ($result['id'] != $user['agencies']['plans']['id'] && $result['is_hidden_plan'] == 'yes') {
					unset($results['data'][$key]);
				}
			}
		}

		return $this->dataWrapper($results);
	}

	public function getById($id, $attributes = [])
	{
		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

		$result = Plans::with($include)->where('id', $id);

		return $result->firstOrFail()->toArray();
	}

	public function create(array $data)
	{
		return $this->atomic(function () use ($data) {
			if ($data['type'] == 'fixed') {
				unset($data['stripe_plan_id']);
				unset($data['interval']);
				unset($data['trial_period']);
			}

			$result = Plans::create($data);

			return $result;
		});
	}

	public function update($id, array $data)
	{
		return $this->atomic(function () use ($id, $data) {
			$result = Plans::where('id', $id)->update($data);

			return $result;
		});
	}

	public function delete($id)
	{
		Plans::where('id', $id)->delete();
	}

	public function search($keyword, $attributes)
	{
		$limit = (@$attributes['limit']) ? $attributes['limit'] : 10;
		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

		$results = $this->queryBuilder(Plans::class, $attributes, $include);

		$results = $results->where('name', 'like', '%' . $keyword . '%');

		$results = $results->paginate($limit)->toArray();

		return $this->dataWrapper($results);
	}
}
