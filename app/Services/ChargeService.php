<?php

namespace App\Services;

use App\Mail\FreeSubscribe;
use App\Models\Agencies;

class ChargeService extends BaseService
{
    protected $agencyService;

    protected $planServices;

    protected $userServices;

    public function __construct(AgencyServices $agencyService, PlanServices $planServices, UserServices $userServices)
    {
        $this->agencyService = $agencyService;
        $this->planServices  = $planServices;
        $this->userServices  = $userServices;
    }

    public function charge($user, $planId, $paymentId)
    {
        return $this->atomic(function () use ($user, $planId, $paymentId) {
            $agency = Agencies::find($user['agencies'][0]['id']);

            $plan = $this->planServices->getById($planId);

            if ($agency->subscribed('SR Subscriptions')) {
                $agency->subscription('SR Subscriptions')->cancel();

                $agency->charge($plan['price'] * 100, $paymentId);

                $this->agencyService->updateAgencyPlan($user, $planId);

                Agencies::where('id', $user['agencies'][0]['id'])->update(['paid_in_advance' => true, 'status' => 'active']);

                return ['success' => true, 'message' => 'Plan Updated'];
            } else {
                $agency->charge($plan['price'] * 100, $paymentId);

                $this->agencyService->updateAgencyPlan($user, $planId);

                Agencies::where('id', $user['agencies'][0]['id'])->update(['paid_in_advance' => true, 'status' => 'active']);

                return ['success' => 'true'];
            }
        });
    }
}
