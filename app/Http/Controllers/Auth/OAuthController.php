<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OAuthProviders;
use App\Services\HubspotServices;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
	use AuthenticatesUsers;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(HubspotServices $hubspotServices)
	{
		$this->client = new Client();
		$this->hubspotServices = $hubspotServices;
	}

	public function integrateToProvider(Request $request)
	{
		$data = $request->all();
		$data['user_id'] = $request->user()->id;

		if (OAuthProviders::where('provider_user_id', $data['provider_user_id'])->where('refresh_token', $data['refresh_token'])->where('user_id', $data['user_id'])->exists()) {
			OAuthProviders::where('provider_user_id', $data['provider_user_id'])->where('refresh_token', $data['refresh_token'])->where('user_id', $data['user_id'])->update([
				'access_token' => $data['access_token'],
				'refresh_token' => $data['refresh_token'],
			]);
			return response(OAuthProviders::where('provider_user_id', $data['provider_user_id'])->where('refresh_token', $data['refresh_token'])->firstOrFail()->toArray());
		} else {
			return response(OAuthProviders::create($data), 201);
		}
	}

	/**
	 * Redirect the user to the provider authentication page.
	 *
	 * @param  string $provider
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function redirectToProvider($provider)
	{
		$url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

		$url = str_replace('scope=', 'scope=' . rawurlencode('oauth content files'), $url);

		return [
			'url' => $url,
		];
	}

	/**
	 * Obtain the user information from the provider.
	 *
	 * @param  string $driver
	 * @return \Illuminate\Http\Response
	 */
	public function handleProviderCallback($provider)
	{
		$res = Socialite::driver($provider)->stateless()->user();

		$res = (array) $res;
		$res['provider'] = $provider;

		return view('oauth/callback', $res);
	}

	/**
	 * Refresh access token
	 *
	 * @param Request $request
	 * @return void
	 */
	public function refreshAccessToken(Request $request, $portalId)
	{
		$oauth = OAuthProviders::where('id', $portalId)->firstOrFail()->toArray();

		$response = \Cache::remember('hubspot-token-' . $oauth['user_id'], 360, function () use ($oauth) {
			return $this->hubspotServices->refreshToken($oauth['refresh_token']);
		});

		return $response;
	}
}
