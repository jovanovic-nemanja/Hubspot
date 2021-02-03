<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserServices;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserServices $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get all users
     *
     * @param Request $request
     * @return void
     */
    public function getAll(Request $request)
    {
        $attributes = $request->all();

        $user = $request->user();

        $res = $this->userService->getAll($attributes, $user);

        return response($res);
    }

    /**
     * Get user by logged in user
     *
     * @param Request $request
     * @return void
     */
    public function getByLoggedUser(Request $request)
    {
        $attributes = $request->all();
        

        $user = $request->user();

        $res = $this->userService->getByLoggedUser($user->id, $attributes);

        return response($res);
    }

    /**
     * Get user by given id
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function getById(Request $request, $id)
    {
        $attributes = $request->all();

        $res = $this->userService->getById($id, $attributes);

        return response($res);
    }

    /**
     * Create new user
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $data = $request->all();

        return response($this->userService->create($data, $request->user()), 201);
    }

    /**
     * Update user by given id
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'role' => 'required',
        ]);

        $data = $request->all();

        return response($this->userService->update($id, $data));
    }

    /**
     * Delete delete logged in user by request
     *
     * @param Request $request
     * @return void
     */
    public function deleteMe(Request $request)
    {
        return response($this->userService->deleteMe($request->user()));
    }

    /**
     * Delete user by given id
     *
     * @param [type] $id
     * @return void
     */
    public function delete($id)
    {
        return response($this->userService->delete($id));
    }

    /**
     * Send email invitation to targeted user
     *
     * @param Request $request
     * @return void
     */
    public function invite(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'role' => 'required',
        ]);

        $data = $request->all();

        return response($this->userService->invite($data, $request->user()));
    }

    /**
     * Accept user invitation
     *
     * @param Request $request
     * @return void
     */
    public function acceptInvitation(Request $request)
    {
        $data = $request->all();

        return response($this->userService->acceptInvitation($data));
    }

    /**
     * Search user
     *
     * @param Request $request
     * @param [type] $keyword
     * @return void
     */
    public function search(Request $request, $keyword)
    {
        $attributes = $request->all();

        $res = $this->userService->search($keyword, $attributes, $request->user());

        return response($res);
    }
}
