<?php

namespace App\Services;

use App\Mail\FreeSubscribe;
use Carbon\Carbon;
use App\Mail\Subscribe;
use App\Models\Agencies;
use App\Models\AgencyBillings;
use App\Models\Plans;
use Laravel\Cashier\Subscription;

class SubscriptionService extends BaseService
{
	protected $agencyService;

	protected $planServices;

	protected $userServices;

	public function __construct(AgencyServices $agencyService, PlanServices $planServices, UserServices $userServices)
	{
		$this->agencyService = $agencyService;
		$this->planServices = $planServices;
		$this->userServices = $userServices;
	}

	public function updateSubscription($user, $planId, $paymentId)
	{
		return $this->atomic(function () use ($user, $planId, $paymentId) {
			$agency = Agencies::find($user['agencies'][0]['id']);

			$billing = AgencyBillings::where('agency_id', $agency->id)->firstOrFail()->toArray();

			$plan = $this->planServices->getById($planId);

			if (!is_null($plan['stripe_plan_id'])) {
				if (!$agency->subscribed('SR Subscriptions')) {
					$agency->newSubscription('SR Subscriptions', $plan['stripe_plan_id'])
						->create($paymentId);
				} else {
					$agency->subscription('SR Subscriptions')->swap($plan['stripe_plan_id']);
				}
			} else {
				abort(422, 'Stripe plan id is empty, Please contact the administrator.');
			}

			$this->agencyService->updateAgencyPlan($user, $plan['id']);

			$this->checkAndActivateAgency($user);

			$mailAttributes = [
				'admin_name' => $billing['name'],
				'plan_name' => $plan['name']
			];

			\Mail::to($billing['email'])->send(new Subscribe($mailAttributes));

			return ['success' => 'true'];
		});
	}

	public function cancelSubscription($user, $plan)
	{
		if ($user->roles[0]->name == 'agency-admin') {
			$agency = Agencies::find($user['agencies'][0]['id']);

			if ($agency->subscribed('SR Subscriptions')) {
				$mailAttributes = [
					'admin_name' => $user['name'],
					'plan_name' => $plan['name']
				];

				\Mail::to($user['email'])->send(new FreeSubscribe($mailAttributes));

				if ($agency->subscription('SR Subscriptions')->onGracePeriod()) {
					return ['success' => true, 'message' => 'On Grace Period'];
				} else {
					$agency->subscription('SR Subscriptions')->cancel();

					return ['success' => true, 'message' => 'Plan Canceled'];
				}
			} else {
				return ['success' => true, 'message' => 'Sorry, you don\'t have any plan subscribed'];
			}
		} else {
			abort(401, 'Sorry, You are not authorized');
		}
	}

	public function getPaymentMethods($user)
	{
		$methods = array();

		$agency = Agencies::find($user['agencies'][0]['id']);

		if ($agency->hasPaymentMethod()) {
			foreach ($agency->paymentMethods() as $method) {
				array_push($methods, [
					'id' => $method->id,
					'brand' => $method->card->brand,
					'last_four' => $method->card->last4,
					'exp_month' => $method->card->exp_month,
					'exp_year' => $method->card->exp_year,
				]);
			}
		}

		return $methods;
	}

	public function postPaymentMethods($user, $paymentMethodId, $billing)
	{
		return $this->atomic(function () use ($user, $paymentMethodId, $billing) {
			$agency = Agencies::find($user['agencies'][0]['id']);

			if ($agency->stripe_id == null) {
				$agency->createAsStripeCustomer();
			}

			$agency->updateStripeCustomer(['name' => $agency->name, 'email' => $user['email']]);

			$agency->addPaymentMethod($paymentMethodId);
			$agency->updateDefaultPaymentMethod($paymentMethodId);

			$methods = array();

			if ($agency->hasPaymentMethod()) {
				foreach ($agency->paymentMethods() as $method) {
					array_push($methods, [
						'id' => $method->id,
						'brand' => $method->card->brand,
						'last_four' => $method->card->last4,
						'exp_month' => $method->card->exp_month,
						'exp_year' => $method->card->exp_year,
						'zip' => $method->billing_details->address->postal_code,
					]);
				}
			}


			if (!AgencyBillings::where('agency_id', $agency->id)->exists()) {
				AgencyBillings::create([
					'name' => $billing['name'],
					'email' => $billing['email'],
					'country' => $billing['country'],
					'zip' => $methods[0]['zip'],
					'agency_id' => $agency->id,
				]);
			} else {
				AgencyBillings::where('agency_id', $agency->id)->update([
					'name' => $billing['name'],
					'email' => $billing['email'],
					'country' => $billing['country'],
					'zip' => $methods[0]['zip'],
				]);
			}

			return $methods[0];
		});
	}

	public function removePaymentMethod($user, $paymentMethodId)
	{
		$agency = Agencies::find($user['agencies'][0]['id']);

		$paymentMethods = $agency->paymentMethods();

		foreach ($paymentMethods as $method) {
			if ($method->id == $paymentMethodId) {
				$method->delete();
				break;
			}
		}

		return ['success' => 'true'];
	}

	public function checkInactiveSubscriptions()
	{
		$subscriptions = Subscription::whereNotNull('ends_at')->whereNotNull('agencies_id')->whereDate('ends_at', '<=', Carbon::now())->get()->toArray();
		$plan = Plans::where('type', 'free')->firstOrFail();

		foreach ($subscriptions as $key => $subscription) {
			$agency = Agencies::where('id', $subscription['agencies_id'])->firstOrFail();

			$agency->plans()->sync($plan->id);
		}
	}

	protected function checkAndActivateAgency($user)
	{
		$user = $this->userServices->getByLoggedUser($user->id);
		$agency = $user['agencies'];

		if ($agency['status'] == 'suspend') {
			Agencies::where('id', $agency['id'])->update(['status' => 'active']);
		}
	}
}
