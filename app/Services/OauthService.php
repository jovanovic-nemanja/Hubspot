<?php

namespace App\Services;

use App\Models\OAuthProviders;
use App\Models\PortalOauthProviders;
use App\Models\Portals;
use GuzzleHttp\Client;

class OauthServices extends BaseService
{
	protected $client;

	protected $userServices;

	public function __construct(UserServices $userServices)
	{
		$this->client = new Client();
		$this->userServices = $userServices;
	}

	public function refreshAccessToken($portal, $user, $createPortal = false)
	{
		if (!is_array($user)) {
			$user = $this->userServices->getByLoggedUser($user->id);
		}

		if (!$createPortal) {
			$portalOauthProviders = PortalOauthProviders::where('portal_id', $portal['id'])->where('user_id', $user['id'])->firstOrFail()->toArray();
			$oauth = OAuthProviders::where('id', $portalOauthProviders['oauth_provider_id'])->firstOrFail()->toArray();
		} else {
			$oauth = OAuthProviders::where('id', $portal['oauth_provider_id'])->firstOrFail()->toArray();
		}

		$response = \Cache::remember('hubspot-token-' . $oauth['refresh_token'], 360, function () use ($oauth) {
			$response = $this->client->post(env('HUBSPOT_API') . '/oauth/v1/token', [
				'form_params' => [
					'grant_type' => 'refresh_token',
					'client_id' => env('HUBSPOT_KEY'),
					'client_secret' => env('HUBSPOT_SECRET'),
					'refresh_token' => $oauth['refresh_token'],
				],
			]);

			return json_decode($response->getBody(), true);
		});

		return $response;
	}

	public function sync()
	{
		$portals = Portals::get()->toArray();

		foreach ($portals as $key => $portal) {
			PortalOauthProviders::create([
				'portal_id'         => $portal['id'],
				'user_id'           => $portal['user_id'],
				'oauth_provider_id' => $portal['oauth_provider_id']
			]);
		}
	}
}
