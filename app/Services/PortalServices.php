<?php

namespace App\Services;

use App\Models\PortalOauthProviders;
use App\Models\Portals;
use App\User;

class PortalServices extends BaseService
{
	protected $modulesDir;

	protected $oauthServices;

	protected $hubspotServices;

	protected $moduleServices;

	protected $userServices;

	public function __construct(OauthServices $oauthServices, HubspotServices $hubspotServices, ModuleServices $moduleServices, UserServices $userServices)
	{
		$this->modulesDir      = storage_path('templates/launchpad-starter-v2/sr/sr-templates/');
		$this->oauthServices   = $oauthServices;
		$this->hubspotServices = $hubspotServices;
		$this->moduleServices  = $moduleServices;
		$this->userServices    = $userServices;
	}

	public function getAll($attributes, $user)
	{
		$limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

		$results = $this->queryBuilder(Portals::class, $attributes, $include);

		if ($user->roles[0]->name != 'admin') {
			$results = $results->whereHas('agency', function ($query) use ($user) {
				if ($user->roles[0]->name == 'agency-admin') {
					$query->where('agency_id', '=', $user->agencies[0]->id);
				} else if ($user->roles[0]->name == 'user') {
					$query->where('agency_id', '=', $user->agencies[0]->id)->where('user_id', '=', $user->id);
				} else {
					$query->where('agency_id', '=', $user->agencies[0]->id);
				}
			});
		}

		$results = $results->paginate($limit)->toArray();

		foreach ($results['data'] as $key => $value) {
			$exists = PortalOauthProviders::where('user_id', $user->id)->where('portal_id', $value['id'])->exists();

			if ($exists) {
				$results['data'][$key]['is_lock'] = true;
			} else {
				$results['data'][$key]['is_lock'] = false;
			}
		}

		return $this->dataWrapper($results);
	}

	public function getById($id, $attributes = [], $user)
	{
		$user = $this->userServices->getByLoggedUser($user->id);

		$include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

		$result = Portals::with($include)->where('id', $id);

		$result = $result->firstOrFail()->toArray();

		$portalStatus = $this->checkPortalSetup($result, $user);

		$result['portal_status'] = $portalStatus;

		return $result;
	}

	public function checkPortalExists($hubId)
	{
		if (Portals::where('hub_id', $hubId)->exists()) {
			return [
				'status' => 'success',
				'is_exists' => true,
			];
		} else {
			return [
				'status' => 'success',
				'is_exists' => false,
			];
		}
	}

	public function create(array $data, $user)
	{
		$user = User::where('id', $user->id)->with('agencies.plans')->firstOrFail()->toArray();

		if (Portals::where('hub_id', $data['hub_id'])->where('agency_id', $user['agencies'][0]['id'])->exists()) {
			$portal = Portals::where('hub_id', $data['hub_id'])->where('agency_id', $user['agencies'][0]['id'])->firstOrFail()->toArray();

			PortalOauthProviders::create([
				'portal_id'         => $portal['id'],
				'user_id'           => $user['id'],
				'oauth_provider_id' => $data['oauth_provider_id']
			]);

			$result['portal_setup_status'] = $this->checkPortalSetup($portal, $user);

			return $result;
		} else {
			if ($user['agencies'][0]['portals_count'] >= $user['agencies'][0]['plans'][0]['portal_limit']) {
				abort(403, 'Portal limit exceeded');
			} else if ($user['agencies'][0]['status'] == 'suspend') {
				abort(403, 'Please update your Billing Information to continue using Sprocket Rocket.');
			} else {
				$data['user_id'] = $user['id'];
				$data['agency_id'] = $user['agencies'][0]['id'];

				return $this->atomic(function () use ($data, $user) {
					if (Portals::where('hub_id', $data['hub_id'])->where('user_id', $data['user_id'])->exists()) {
						$portal = Portals::where('hub_id', $data['hub_id'])->where('user_id', $data['user_id'])->firstOrFail()->toArray();
						$result['portal_setup_status'] = $this->checkPortalSetup($portal, $user);
					} else {
						$result = Portals::create($data)->toArray();
						$result['portal_setup_status'] = $this->checkPortalSetup($result, $user, true);
					}

					PortalOauthProviders::create([
						'portal_id'         => $result['id'],
						'user_id'           => $data['user_id'],
						'oauth_provider_id' => $data['oauth_provider_id']
					]);

					return $result;
				});
			}
		}
	}

	public function setupWizard(array $data, $user)
	{
		$user = $this->userServices->getByLoggedUser($user->id);

		$result['setup_result'] = $this->runSetupWizard($data, $user);

		return $result;
	}

	public function updateWizard(array $data, $user)
	{
		$user = $this->userServices->getByLoggedUser($user->id);

		$result['update_result'] = $this->runUpdateWizard($data, $user);

		return $result;
	}

	public function update($id, array $data)
	{
		return $this->atomic(function () use ($id, $data) {
			$result = Portals::where('id', $id)->update($data);

			return $result;
		});
	}

	public function delete($id)
	{
		Portals::where('id', $id)->delete();
	}

	public function getModulesById($id, $user)
	{
		if ($user->roles[0]->name == 'admin') {
			$portal = Portals::where('id', $id)->firstOrFail()->toArray();

			$accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

			$res = $this->hubspotServices->getRemoteModules($accessToken['access_token']);

			return $res;
		} else {
			abort(401, 'Sorry, sorry you don\'t have permission to access this page');
		}
	}

	public function getPagesById($id, $user)
	{
		if ($user->roles[0]->name == 'admin') {
			$portal = Portals::where('id', $id)->firstOrFail()->toArray();

			$accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

			$res = $this->hubspotServices->getAllPages($accessToken['access_token']);

			return $res;
		} else {
			abort(401, 'Sorry, sorry you don\'t have permission to access this page');
		}
	}

	public function getTemplatesById($id, $user)
	{
		if ($user->roles[0]->name == 'admin') {
			$portal = Portals::where('id', $id)->firstOrFail()->toArray();

			$accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

			$res = $this->hubspotServices->getAllTemplate($accessToken['access_token']);

			return $res;
		} else {
			abort(401, 'Sorry, sorry you don\'t have permission to access this page');
		}
	}

	public function checkPortalAccess($id, $user)
	{
		$isExists = PortalOauthProviders::where('user_id', $user->id)->where('portal_id', $id)->exists();

		if ($isExists) {
			return ['status' => true];
		} else {
			return ['status' => false];
		}
	}

	protected function runSetupWizard($data, $user)
	{
		$setupJson = file_get_contents(storage_path('templates/launchpad-starter-v2/sr-setup.json'));
		$setupJson = json_decode($setupJson, true);

		$template = $this->uploadTemplate($data, $setupJson['template'], $user);
		$files = [];
		$customModules = $this->uploadCustomModulesFiles($data, $setupJson['custom_modules'], $user);

		return [
			'templates' => $template,
			'files' => $files,
			'customModules' => $customModules
		];
	}

	protected function runUpdateWizard($data, $user)
	{
		$setupJson = file_get_contents(storage_path('templates/launchpad-starter-v2/sr-update.json'));
		$setupJson = json_decode($setupJson, true);

		$template = $this->uploadTemplate($data, $setupJson['template'], $user);
		$files = [];
		$customModules = $this->uploadCustomModulesFiles($data, $setupJson['custom_modules'], $user);

		return [
			'templates' => $template,
			'files' => $files,
			'customModules' => $customModules
		];
	}

	protected function uploadCustomModulesFiles($data, $customModules, $user)
	{
		$results = [];

		foreach ($customModules as $customModule) {
			$accessToken = $this->oauthServices->refreshAccessToken($data, $user);

			$result = $this->hubspotServices->uploadFileMapper($accessToken['access_token'], $customModule);

			$uploadRes = [
				'path' => $customModule["parentPath"] . $customModule["name"],
				'result' => $result,
			];

			array_push($results, $uploadRes);
		}

		return $results;
	}

	protected function uploadCustomModules($data, $customModules, $user)
	{
		$results = [];

		foreach ($customModules as $customModule) {
			$accessToken = $this->oauthServices->refreshAccessToken($data, $user);

			$setupJson = file_get_contents($this->modulesDir . $customModule);
			$setupJson = json_decode($setupJson, true);

			$results = $this->moduleServices->uploadModule($setupJson, $accessToken);

			$uploadRes = [
				'path' => $customModule,
				'status' => 'success',
			];

			array_push($results, $uploadRes);
		}

		return $results;
	}

	protected function uploadTemplate($data, $templates, $user)
	{
		$results = [];

		$accessToken = $this->oauthServices->refreshAccessToken($data, $user);

		foreach ($templates as $key => $template) {
			$template['source'] = file_get_contents(storage_path('templates') . '/' . $template['source']);

			$response = $this->hubspotServices->uploadTemplate($accessToken['access_token'], $template);

			if ($response->getStatusCode() == 201) {
				$uploadRes = [
					'path' => $template['path'],
					'result' => json_decode($response->getBody(), true)
				];
				array_push($results, $uploadRes);
			} else {
				$uploadRes = [
					'path' => $template['path'],
					'result' => json_decode($response->getBody(), true)
				];
				array_push($results, $uploadRes);
			}
		}

		return $results;
	}

	protected function checkPortalSetup($portal, $user, $createPortal = false)
	{
		$accessToken = $this->oauthServices->refreshAccessToken($portal, $user, $createPortal);

		$template = $this->hubspotServices->checkTemplate($accessToken['access_token']);
		$template = json_decode($template->getBody(), true);

		if (@$template['total']) {
			return true;
		} else {
			return false;
		}
	}
}
