<?php

namespace App\Http\Controllers;

use App\Services\ChargeService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService, ChargeService $chargeService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->chargeService       = $chargeService;
    }

    /**
     * Setup Intent
     *
     * @param Request $request
     * @return void
     */
    public function getSetupIntent(Request $request)
    {
        return $request->user()->createSetupIntent();
    }

    /**
     * Updates a subscription for the user
     * 
     * @param Request $request The request containing subscription update info.
     */
    public function updateSubscription(Request $request)
    {
        $user      = $request->user();
        $planId    = $request->get('plan');
        $paymentId = $request->get('payment');

        $this->subscriptionService->updateSubscription($user, $planId, $paymentId);

        return response(['subscription_updated' => true]);
    }

    /**
     * Cancel a subscription for the user
     *
     * @param Request $request
     * @return void
     */
    public function cancelSubscription(Request $request)
    {
        $user = $request->user();
        $plan = $request->get('plan');

        $res = $this->subscriptionService->cancelSubscription($user, $plan);

        return response($res);
    }

    /**
     * Adds a payment method to the current user. 
     * 
     * @param Request $request The request data from the user.
     */
    public function postPaymentMethods(Request $request)
    {
        $user            = $request->user();
        $paymentMethodId = $request->get('payment_method');
        $billing         = $request->get('billing');

        $res = $this->subscriptionService->postPaymentMethods($user, $paymentMethodId, $billing);

        return response($res);
    }

    /**
     * Returns the payment methods the user has saved
     * 
     * @param Request $request The request data from the user.
     */
    public function getPaymentMethods(Request $request)
    {
        $user = $request->user();

        $res = $this->subscriptionService->getPaymentMethods($user);

        return response($res);
    }

    /**
     * Removes a payment method for the current user.
     * 
     * @param Request $request The request data from the user.
     */
    public function removePaymentMethod(Request $request)
    {
        $user            = $request->user();
        $paymentMethodId = $request->get('id');

        $this->subscriptionService->removePaymentMethod($user, $paymentMethodId);

        return response()->json(null, 204);
    }

    public function charge(Request $request)
    {
        $user      = $request->user();
        $planId    = $request->get('plan');
        $paymentId = $request->get('payment');

        $res = $this->chargeService->charge($user, $planId, $paymentId);

        return response($res);
    }
}
