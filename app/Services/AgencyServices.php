<?php

namespace App\Services;

use App\Mail\AgencyDeleteNotification;
use App\Mail\AgencyInvitation;
use App\Mail\SRInvitation;
use App\Models\Agencies;
use App\Models\AgencyBillings;
use App\Models\AgencyInvitations;
use App\Models\Plans;
use App\Models\Portals;
use App\User;
use Illuminate\Support\Str;
use Mail;
use Spatie\Permission\Models\Role;

use Config;

class AgencyServices extends BaseService
{
    protected $userServices;

    protected $hubspotServices;

    public function __construct(UserServices $userServices, HubspotServices $hubspotServices)
    {
        $this->userServices = $userServices;
        $this->hubspotServices = $hubspotServices;
    }

    public function getAll($attributes, $user)
    {
        $user = $this->userServices->getByLoggedUser($user->id);

        $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $results = $this->queryBuilder(Agencies::class, $attributes, $include);

        if ($user['roles']['name'] != 'admin') {
            $results = $results->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', '=', $user['id']);
            });
        }

        $results = $results->paginate($limit)->toArray();

        return $this->dataWrapper($results);
    }

    public function create(array $data, $user)
    {
        return $this->atomic(function () use ($data, $user) {
            $password = Str::random(10);

            $agencyAdminData = [
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => bcrypt($password)
            ];

            $user = User::create($agencyAdminData);

            $role = Role::where('name', 'agency-admin')->firstOrFail();

            $user->assignRole($role); // Assign role to user

            $agencyData = [
                'name' => $data['name'],
                'admin_name' => $data['admin_name'],
                'admin_email' => $data['admin_email'],
                'status' => 'active',
                'agency_plan' => (@$data['agency_plan']) ? 1 : 0,
                'paid_in_advance' => (@$data['paid_in_advance']) ? 1 : 0,
            ];

            $agency = Agencies::create($agencyData);

            $agency->users()->save($user); // Add user to an agency

            $plan = Plans::where('id', $data['plan_id'])->firstOrFail();

            $agency->plans()->attach($plan->id); // Add plan to an agency

            $mailAttributes = [
                'name' => $data['name'],
                'admin_name' => $data['admin_name'],
                'admin_email' => $data['admin_email'],
                'password' => $password
            ];

            Mail::to($data['admin_email'])->send(new AgencyInvitation($mailAttributes));

            return $agency;
        });
    }

    public function invite(array $data)
    {
        if (User::where('email', $data['email'])->exists()) {
            abort(422, 'An account using ' . $data['email'] . ' already exists.');
        } else {
            return $this->atomic(function () use ($data) {
                $token = md5($data['email']);
                $adminName = @$data['name'] ? $data['name'] : '';
                $agencyName = @$data['agency_name'] ? $data['agency_name'] : '';

                $agencyData = [
                    'agency_name' => $agencyName,
                    'admin_name' => $adminName,
                    'admin_email' => $data['email'],
                    'plan_id' => $data['plan_id'],
                    'redirect_url' => $data['redirect_url'],
                    'token' => $token
                ];

                $agency = AgencyInvitations::create($agencyData);

                // Send Email
                $mailAttributes = [
                    'name' => $adminName,
                    'link' => env('APP_URL') . '/accept-invitation?token=' . $token,
                ];

                Mail::to($data['email'])->send(new SRInvitation($mailAttributes));

                return $agency;
            });
        }
    }

    public function regist(array $data)
    {
        if (User::where('email', $data['email'])->exists()) {
            abort(422, 'An account using ' . $data['email'] . ' already exists.');
        } else {
            return $this->atomic(function () use ($data) {
                $token = md5($data['email']);
                $adminName = @$data['name'] ? $data['name'] : '';
                $agencyName = @$data['agency_name'] ? $data['agency_name'] : '';

                $agencyData = [
                    'agency_name' => $agencyName,
                    'admin_name' => $adminName,
                    'admin_email' => $data['email'],
                    'plan_id' => $data['plan_id'],
                    'redirect_url' => $data['redirect_url'],
                    'token' => $token
                ];

                $agency = AgencyInvitations::create($agencyData);

                $agency['link'] = env('APP_URL') . '/accept-invitation?token=' . $token;

                return $agencyData;
            });
        }
    }

    public function getInvite($token)
    {
        $res = AgencyInvitations::where('token', $token)->firstOrFail()->toArray();

        return $res;
    }

    public function acceptInvitation($data)
    {
        return $this->atomic(function () use ($data) {
            $plan = Plans::where('id', $data['plan_id'])->firstOrFail();
            
            // Create Hubspot Contact
            $contactAttributes = array(
                'properties' => array(
                    array(
                        'property' => 'email',
                        'value' => $data['email']
                    ),
                    array(
                        'property' => 'firstname',
                        'value' => $data['name']
                    ),
                    array(
                        'property' => 'company',
                        'value' => $data['agency_name']
                    ),
                    array(
                        'property' => 'website',
                        'value' => $data['url']
                    ),
                    array(
                        'property' => 'sr_app_plan',
                        'value' => $plan->name
                    )
                )
            );

            $res = $this->hubspotServices->createContact($contactAttributes);

            if (@$res['status'] == 'error' && @$res['error'] == 'CONTACT_EXISTS') { // Check if HS contact already exists
                // Update contact property
                $res = $this->hubspotServices->updateContact($res['identityProfile']['vid'], $contactAttributes);

                if (Agencies::where('admin_email', $data['email'])->exists()) { // Check if agency admin email already exists
                    abort(409, 'Email already exists.');
                } else { // Agency admin email doesn't exists
                    // Create agency
                    return $this->createAgency($data);
                }
            } else { // HS Contact doesn't exists
                if (Agencies::where('admin_email', $data['email'])->exists()) { // Check if agency admin email already exists
                    abort(409, 'Email already exists.');
                } else { // Agency admin email doesn't exists
                    // Create agency
                    return $this->createAgency($data);
                }
            }
        });
    }

    protected function createAgency($data)
    {
        $agencyAdminData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ];

        $env = Config::get('app.APP_DIRECT_REGISTER');
        if($env == true) {
            // Send Email
            $mailAttributes = [
                'name' => $data['name'],
                'admin_name' => $data['name'],
                'admin_email' => $data['email'],
                'password' => $data['password']
            ];
            
            Mail::to($data['email'])->send(new AgencyInvitation($mailAttributes));
        }

        $user = User::create($agencyAdminData);

        $role = Role::where('name', 'agency-admin')->firstOrFail();

        $user->assignRole($role); // Assign role to user

        $agencyData = [
            'name' => $data['agency_name'],
            'admin_name' => $data['name'],
            'admin_email' => $data['email'],
            'status' => 'active'
        ];

        $agency = Agencies::create($agencyData);

        $agency->users()->save($user); // Add user to an agency

        $agency->plans()->attach($data['plan_id']); // Add plan to an agency

        return $agency;
    }

    public function update($id, array $data)
    {
        return $this->atomic(function () use ($id, $data) {
            $agencyData = [
                'name' => $data['name'],
                'admin_name' => $data['admin_name'],
                'admin_email' => $data['admin_email'],
                'cover' => $data['cover'],
                'status' => $data['status'],
                'agency_plan' => (@$data['agency_plan']) ? 1 : 0,
                'paid_in_advance' => (@$data['paid_in_advance']) ? 1 : 0,
            ];

            Agencies::where('id', $id)->update($agencyData);

            $agency = Agencies::where('id', $id)->firstOrFail();

            $plan = Plans::where('id', $data['plan_id'])->firstOrFail();

            $agency->plans()->sync($plan->id);

            return $this->getById($id);
        });
    }

    public function getById($id, $attributes = [])
    {
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $result = Agencies::with($include)->where('id', $id);

        $agency = $result->firstOrFail()->toArray();

        $agency['plan_id'] = @$agency['plans'] ? $agency['plans'][0]['id'] : [];
        $agency['plan'] = @$agency['plans'] ? $agency['plans'][0] : [];
        unset($agency['plans']);

        return $agency;
    }

    public function updateAgencyPlan($user, $planId)
    {
        return $this->atomic(function () use ($user, $planId) {
            $agency = Agencies::whereHas('users', function ($query) use ($user) {
                $query->where('user_id', '=', $user->id);
            })->firstOrFail();

            $agency->plans()->sync($planId);

            return ['status' => 'success'];
        });
    }

    public function deleteMe($user)
    {
        return $this->atomic(function () use ($user) {
            if ($user->roles[0]->name == 'agency-admin') {
                $agency = Agencies::whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                })->firstOrFail()->toArray();

                Portals::whereHas('agency', function ($query) use ($agency) {
                    $query->where('agency_id', '=', $agency['id']);
                })->delete();

                User::whereHas('agencies', function ($query) use ($agency) {
                    $query->where('agency_id', '=', $agency['id']);
                })->delete();

                $mailAttributes = [];

                Mail::to($user->email)->send(new AgencyDeleteNotification($mailAttributes));

                $agencyObject = Agencies::find($agency['id']);

                if ($agencyObject->subscribed('SR Subscriptions')) {
                    $agencyObject->subscription('SR Subscriptions')->cancel();
                }

                return $this->delete($agency['id']);
            } else {
                abort(401, 'Sorry, You are not authorized delete agency');
            }
        });
    }

    public function delete($id)
    {
        Agencies::where('id', $id)->delete();
    }

    public function search($keyword, $attributes, $user)
    {
        $user = $this->userServices->getByLoggedUser($user->id);

        $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $results = $this->queryBuilder(Agencies::class, $attributes, $include);

        $results = $results->where('name', 'like', '%' . $keyword . '%');

        if ($user['roles']['name'] != 'admin') {
            $results = $results->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', '=', $user['id']);
            });
        }

        $results = $results->paginate($limit)->toArray();

        return $this->dataWrapper($results);
    }

    public function checkBilling($attributes, $user)
    {
        $user = $this->userServices->getByLoggedUser($user->id);

        if ($user['roles']['name'] != 'admin') {
            $agency = $user['agencies'];

            $agencyObject = Agencies::find($agency['id']);

            if ($agency['plans']['type'] == 'paid' && !AgencyBillings::where('agency_id', $agency['id'])->exists()) {
                Agencies::where('id', $agency['id'])->update(['status' => 'suspend']);

                return [
                    'billing_status' => 'suspend'
                ];
            } else if ($agency['plans']['type'] == 'fixed') {
                if ($agency['paid_in_advance']) {
                    return [
                        'billing_status' => 'active'
                    ];
                } else {
                    Agencies::where('id', $agency['id'])->update(['status' => 'suspend']);

                    return [
                        'billing_status' => 'suspend'
                    ];
                }
            } else {
                if (!$agencyObject->subscribed('SR Subscriptions') && $agency['plans']['type'] != 'free') {
                    Agencies::where('id', $agency['id'])->update(['status' => 'suspend']);

                    return [
                        'billing_status' => 'suspend'
                    ];
                } else {
                    return [
                        'billing_status' => 'active'
                    ];
                }
            }
        } else {
            return [
                'billing_status' => 'active'
            ];
        }
    }
}
