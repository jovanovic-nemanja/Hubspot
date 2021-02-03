<?php

namespace App\Services;

use GuzzleHttp\Client;
use Cache;

class HubspotServices extends BaseService
{
	protected $modulesDir;

	protected $filesDir;

	protected $client;

	public function __construct()
	{
		$this->modulesDir = storage_path('templates/launchpad-starter-v2/sr/sr-templates/');
		$this->filesDir = storage_path('templates/');
		$this->client = new Client();
	}

	public function refreshToken($refreshToken)
	{
		$response = Cache::remember('hubspot-token-' . $refreshToken, 360, function () use ($refreshToken) {
			$response = $this->client->post(env('HUBSPOT_API') . '/oauth/v1/token', [
				'form_params' => [
					'grant_type' => 'refresh_token',
					'client_id' => env('HUBSPOT_KEY'),
					'client_secret' => env('HUBSPOT_SECRET'),
					'refresh_token' => $refreshToken,
				],
			]);

			return json_decode($response->getBody(), true);
		});
	}

	public function getAllPages($accessToken)
	{
		$response = $this->client->get(env('HUBSPOT_API') . '/content/api/v2/pages?limit=1000', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
		]);

		return json_decode($response->getBody(), true);
	}

	public function getAllTemplate($accessToken)
	{
		$response = $this->client->get(env('HUBSPOT_API') . '/content/api/v2/templates?limit=1000', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
		]);

		return json_decode($response->getBody(), true);
	}

	public function createPage($accessToken, $data)
	{
		$response = $this->client->post(env('HUBSPOT_API') . '/content/api/v2/pages', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
			'body' => json_encode($data),
		]);

		$createPageResponse = json_decode($response->getBody(), true);

		if (@$createPageResponse['status'] == 'error' && $data['subcategory'] == 'site_page') {
			abort(403, 'Your HubSpot account type does not allow Website Pages. Please upgrade your account to use this feature.');
		}

		return $createPageResponse;
	}

	public function createAndPublishPage($accessToken, $data, $publishPageData)
	{
		$createPageResponse = $this->client->post(env('HUBSPOT_API') . '/content/api/v2/pages', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
			'body' => json_encode($data),
		]);

		$createPageResponse = json_decode($createPageResponse->getBody(), true);

		if (@$createPageResponse['status'] == 'error' && $data['subcategory'] == 'site_page') {
			abort(403, 'Your HubSpot account type does not allow Website Pages. Please upgrade your account to use this feature.');
		}

		if (isset($createPageResponse['id'])) {
			$this->publishPage($accessToken, $createPageResponse['id'], $publishPageData);
		}

		return $createPageResponse;
	}

	public function publishPage($accessToken, $id, $data)
	{
		$response = $this->client->post(env('HUBSPOT_API') . '/content/api/v2/pages/' . $id . '/publish-action', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
			'body' => json_encode($data),
		]);

		return json_decode($response->getBody(), true);
	}

	public function uploadTemplate($accessToken, $template)
	{
		$response = $this->client->post(env('HUBSPOT_API') . '/content/api/v2/templates', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
			'body' => json_encode($template),
		]);

		return $response;
	}

	public function checkTemplate($accessToken)
	{
		$response = $this->client->get(env('HUBSPOT_API') . '/content/api/v2/templates?path=sr/templates/variables.html', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
		]);

		return $response;
	}

	public function getRemoteModules($accessToken)
	{
		$response = $this->client->get(env('HUBSPOT_API') . '/content/api/v4/custom-widgets?limit=1000', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
		]);

		return json_decode($response->getBody(), true);
	}

	public function getRemoteModulesByName($accessToken, $name)
	{
		$response = $this->client->get(env('HUBSPOT_API') . '/content/api/v4/custom-widgets?name=' . $name, [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
		]);

		return json_decode($response->getBody(), true);
	}

	public function uploadModules($accessToken, $data, $moduleId = null)
	{
		$response = $this->client->post(env('HUBSPOT_API') . '/content/api/v4/custom-widgets' . $moduleId, [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
			'body' => json_encode($data),
		]);

		return json_decode($response->getBody(), true);
	}

	public function upgradeModules($accessToken, $data, $moduleId)
	{
		$response = $this->client->put(env('HUBSPOT_API') . '/content/api/v4/custom-widgets/' . $moduleId . '/buffer/upgrade', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
			'body' => json_encode($data),
		]);

		return json_decode($response->getBody(), true);
	}

	public function publishModules($accessToken, $data, $moduleId)
	{
		$response = $this->client->put(env('HUBSPOT_API') . '/content/api/v4/custom-widgets/' . $moduleId . '/push-buffer-live', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
			'body' => json_encode($data),
		]);

		return json_decode($response->getBody(), true);
	}

	public function getFolderId($accessToken)
	{
		$response = $this->client->get(env('HUBSPOT_API') . '/designmanager/v1/folders?limit=1000', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
				'content-type' => 'application/json',
			],
		]);

		$response = json_decode($response->getBody(), true);

		if (key_exists('objects', $response)) {
			foreach ($response['objects'] as $folder) {
				if ($folder['name'] == 'custom-modules') {
					return $folder['id'];
				}
			}
		} else {
			abort(403, 'Hubspot Api Error, Objects not found');
		}
	}

	public function uploadFiles($accessToken, $file)
	{
		$response = $this->client->post(env('HUBSPOT_API') . '/filemanager/api/v2/files?overwrite=true', [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
			],
			'multipart' => [
				[
					'content-type' => 'multipart/form-data',
					'name' => 'files',
					'contents' => fopen($this->filesDir . $file['file'], 'r'),
				],
				[
					'name' => 'folder_paths',
					'contents' => $file['folder_paths'],
				],
			],
		]);

		return $response;
	}

	public function uploadFileMapper($accessToken, $file)
	{
		$response = $this->client->post(env('HUBSPOT_API') . '/content/filemapper/v1/upload/' . $file['parentPath'] . $file['name'], [
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $accessToken,
			],
			'multipart' => [
				[
					'name' => 'file',
					'contents' => fopen($this->filesDir . $file['path'], 'r'),
				],
			],
		]);

		return json_decode($response->getBody(), true);
	}

	public function createContact($data)
	{
		$response = $this->client->post(env('HUBSPOT_API') . '/contacts/v1/contact?hapikey=' . env('HUBSPOT_API_KEY'), [
			'http_errors' => false,
			'headers' => [
				'content-type' => 'application/json',
			],
			'body' => json_encode($data),
		]);

		return json_decode($response->getBody(), true);
	}

	public function updateContact($vid, $data)
	{
		$response = $this->client->post(env('HUBSPOT_API') . '/contacts/v1/contact/vid/' . $vid . '/profile?hapikey=' . env('HUBSPOT_API_KEY'), [
			'http_errors' => false,
			'headers' => [
				'content-type' => 'application/json',
			],
			'body' => json_encode($data),
		]);

		return json_decode($response->getBody(), true);
	}
}
