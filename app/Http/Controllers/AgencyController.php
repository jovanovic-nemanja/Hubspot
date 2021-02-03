<?php

namespace App\Http\Controllers;

use Config;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\AgencyServices;
use App\Models\AgencyInvitations;
use Auth;
use App\Http\Controllers\AgencyController;

class AgencyController extends Controller
{
    protected $agencyService;

    public function __construct(AgencyServices $agencyService)
    {
        $this->agencyService = $agencyService;
    }

    /**
     * Get all agencies
     *
     * @param Request $request
     * @return void
     */
    public function getAll(Request $request)
    {
        $attributes = $request->all();

        $res = $this->agencyService->getAll($attributes, $request->user());

        return response($res);
    }

    /**
     * Get agency by given id
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function getById(Request $request, $id)
    {
        $attributes = $request->all();

        $res = $this->agencyService->getById($id, $attributes);

        return response($res);
    }

    /**
     * Create new agency
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'admin_name' => 'required',
            'admin_email' => 'required',
        ]);

        $data = $request->all();

        return response($this->agencyService->create($data, $request->user()), 201);
    }

    /**
     * Invite agency
     *
     * @param Request $request
     * @return void
     */
    public function invite(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'plan_id' => 'required',
            'redirect_url' => 'required'
        ]);

        $data = $request->all();

        $env = Config::get('app.APP_DIRECT_REGISTER');
        if($env == true) {
            $datas = $this->agencyService->regist($data);
            $arr = [
                'password' => Str::random(10),
                'agency_name' => $datas['agency_name'],
                'name' => $datas['admin_name'],
                'email' => $datas['admin_email'],
                'url' => $data['url'],
                'plan_id' => $datas['plan_id'],
                'redirect_url' => $datas['redirect_url'],
                'token' => $datas['token']
            ];
            
            $this->agencyService->acceptInvitation($arr);
            $userdata = User::where('email', $datas['admin_email'])->first();
            Auth::login($userdata);

            $url = env('APP_URL') . '/auto-login?email=' . urlencode($arr['email']) . '&&pass=' . urlencode($arr['password']);
            return redirect($url);
        }else{
            $this->agencyService->invite($data);

            return redirect($data['redirect_url']);
        }
    }

    /**
     * Get user data by token
     *
     * @param Request $request
     * @param [type] $token
     * @return void
     */
    public function autologin(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $agencydata = AgencyInvitations::where('token', $request->token)->first();
        $userdata = User::where('email', $agencydata->admin_email)->first();
        Auth::login($userdata);

        $url = env('APP_URL') . '/portals-main';
        return response($request->token);
    }

    /**
     * Get invitation details
     *
     * @param Request $request
     * @param [type] $token
     * @return void
     */
    public function getInvite(Request $request, $token)
    {
        return response($this->agencyService->getInvite($token));
    }

    /**
     * Agency accept invitation
     *
     * @param Request $request
     * @return void
     */
    public function acceptInvitation(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'agency_name' => 'required'
        ]);

        $data = $request->all();

        $res = $this->agencyService->acceptInvitation($data);

        return response($res);
    }

    /**
     * Update agency by given id
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'admin_name' => 'sometimes|required',
            'admin_email' => 'sometimes|required',
        ]);

        $data = $request->all();

        return response($this->agencyService->update($id, $data));
    }

    /**
     * Delete my agency account
     *
     * @param Request $request
     * @return void
     */
    public function deleteMe(Request $request)
    {
        $user = $request->user();

        return response($this->agencyService->deleteMe($user));
    }

    /**
     * Delete agency by given id
     *
     * @param [type] $id
     * @return void
     */
    public function delete($id)
    {
        return response($this->agencyService->delete($id));
    }

    /**
     * Search agency
     *
     * @param Request $request
     * @param [type] $keyword
     * @return void
     */
    public function search(Request $request, $keyword)
    {
        $attributes = $request->all();

        $res = $this->agencyService->search($keyword, $attributes, $request->user());

        return response($res);
    }

    /**
     * Check billing of a agency
     *
     * @param Request $request
     * @return void
     */
    public function checkBilling(Request $request)
    {
        $attributes = $request->all();

        $res = $this->agencyService->checkBilling($attributes, $request->user());

        return response($res);
    }
}
