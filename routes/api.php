<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::prefix('v1')->middleware(['logger'])->group(function () {
	Route::post('users/accept', 'UserController@acceptInvitation');
	Route::post('agencies/invite', 'AgencyController@invite');

	Route::post('agencies/accept-invitation', 'AgencyController@acceptInvitation');
	Route::get('agencies/invite/{token}', 'AgencyController@getInvite');

	Route::middleware('auth:api')->group(function () {
		Route::get('agencies', 'AgencyController@getAll');
		Route::get('agencies/search/{keyword}', 'AgencyController@search');
		Route::get('agencies/check-billing', 'AgencyController@checkBilling');
		Route::get('agencies/{id}', 'AgencyController@getById');
		Route::post('agencies', 'AgencyController@create');
		Route::put('agencies/{id}', 'AgencyController@update');
		Route::delete('agencies/me', 'AgencyController@deleteMe');
		Route::delete('agencies/{id}', 'AgencyController@delete');

		Route::get('plans', 'PlanController@getAll');
		Route::get('plans/search/{keyword}', 'PlanController@search');
		Route::get('plans/{id}', 'PlanController@getById');
		Route::post('plans', 'PlanController@create');
		Route::put('plans/{id}', 'PlanController@update');
		Route::delete('plans/{id}', 'PlanController@delete');

		Route::get('portals', 'PortalController@getAll');
		Route::get('portals/{id}', 'PortalController@getById');
		Route::get('portals/{id}/modules', 'PortalController@getModulesById');
		Route::get('portals/{id}/pages', 'PortalController@getPagesById');
		Route::get('portals/{id}/templates', 'PortalController@getTemplatesById');
		Route::get('portals/check/{hub_id}', 'PortalController@checkPortalExists');
		Route::get('portals/access/{id}', 'PortalController@checkPortalAccess');
		Route::post('portals', 'PortalController@create');
		Route::post('portals/setup-wizard', 'PortalController@setupWizard');
		Route::post('portals/update-wizard', 'PortalController@updateWizard');
		Route::put('portals/{id}', 'PortalController@update');
		Route::delete('portals/{id}', 'PortalController@delete');

		Route::get('pages', 'PageController@getAll');
		Route::get('pages/{id}', 'PageController@getById');
		Route::post('pages', 'PageController@create');
		Route::post('pages/custom', 'PageController@createCustomPage');
		Route::put('pages/{id}', 'PageController@update');
		Route::delete('pages/{id}', 'PageController@delete');


		Route::get('layouts', 'LayoutsController@getAll');
		Route::get('layouts/{id}', 'LayoutsController@getById');
		Route::post('layouts', 'LayoutsController@create');
		Route::post('layouts/custom', 'LayoutsController@createCustomPage');
		Route::put('layouts/{id}', 'LayoutsController@update');
		Route::delete('layouts/{id}', 'LayoutsController@delete');


		Route::get('histories', 'HistoryController@getAll');
		Route::get('histories/{id}', 'HistoryController@getById');
		Route::post('histories', 'HistoryController@create');
		Route::put('histories/{id}', 'HistoryController@update');
		Route::delete('histories/{id}', 'HistoryController@delete');


		Route::get('users', 'UserController@getAll');
		Route::get('users/auth', 'UserController@getByLoggedUser');

		Route::get('users/search/{id}', 'UserController@search');
		Route::get('users/{id}', 'UserController@getById');
		Route::post('users', 'UserController@create');
		Route::post('users/invite', 'UserController@invite');
		Route::put('users/{id}', 'UserController@update');
		Route::delete('users/me', 'UserController@deleteMe');
		Route::delete('users/{id}', 'UserController@delete');

		Route::get('billings/setup-intent', 'BillingController@getSetupIntent');
		Route::get('billings/payment-methods', 'BillingController@getPaymentMethods');
		Route::post('billings/payment-methods', 'BillingController@postPaymentMethods');
		Route::post('billings/remove-payment-methods', 'BillingController@removePaymentMethod');
		Route::put('billings/subscription', 'BillingController@updateSubscription');
		Route::put('billings/cancel-subscription', 'BillingController@cancelSubscription');
		Route::put('billings/charge', 'BillingController@charge');

		Route::post('image-upload', 'ImageUploadController@imageUploadPost');

		Route::get('modules/website-pages', 'ModuleController@getWebsitePages');
		Route::get('modules/landing-pages', 'ModuleController@getLandingPages');
		Route::get('modules/blogs', 'ModuleController@getBlogPages');
		Route::get('modules/system-pages', 'ModuleController@getSystemPages');
		Route::get('modules/categories', 'ModuleController@getAllByCategories');
		Route::get('modules/audit', 'ModuleController@audit');
		Route::get('modules/{portalId}', 'ModuleController@getAll');
		Route::post('modules/install', 'ModuleController@install');
		Route::post('modules/upgrade', 'ModuleController@upgrade');
		Route::get('modules/information/{portalId}', 'ModuleController@upgradeInformation');
	});
});

Route::group(['middleware' => ['auth:api', 'logger']], function () {
	Route::post('logout', 'Auth\LoginController@logout');

	Route::get('/user', function (Request $request) {
		return $request->user();
	});

	Route::patch('settings/profile', 'Settings\ProfileController@update');
	Route::patch('settings/password', 'Settings\PasswordController@update');

	Route::get('oauth/refresh/{portalId}', 'Auth\OAuthController@refreshAccessToken');
	Route::post('oauth/integrate', 'Auth\OAuthController@integrateToProvider');
	Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
	Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');
});

Route::group(['middleware' => ['guest:api', 'logger']], function () {
	Route::post('login', 'Auth\LoginController@login');
	Route::post('autologin', 'AgencyController@autologin');
	Route::post('register', 'Auth\RegisterController@register');

	Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
	Route::post('password/reset', 'ResetPasswordController@reset');
	Route::post('password/setup', 'ResetPasswordController@setup');

	Route::post('email/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
	Route::post('email/resend', 'Auth\VerificationController@resend');
});

Route::get('/debug', function (Request $request) {
	$debugStacks = array_reverse(\Cache::get('debug'));

	if (is_null($debugStacks)) {
		r('No Debug Stack');
	} else {
		foreach ($debugStacks as $debugStack) {
			r($debugStack);
		}
	}
});
