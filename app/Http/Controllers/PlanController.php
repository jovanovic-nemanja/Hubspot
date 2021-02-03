<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlanServices;

class PlanController extends Controller
{
    protected $planService;

    public function __construct(PlanServices $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Get all plans
     *
     * @param Request $request
     * @return void
     */
    public function getAll(Request $request)
    {
        $attributes = $request->all();

        $res = $this->planService->getAll($attributes, $request->user());

        return response($res);
    }

    /**
     * Get plan by given id
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function getById(Request $request, $id)
    {
        $res = $this->planService->getById($id);

        return response($res);
    }

    /**
     * Create new plan
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
            'portal_limit' => 'required',
            'user_limit' => 'required',
            'module_limit' => 'required',
            'is_free_module' => 'required',
            'is_hidden_plan' => 'required',
            // 'price' => 'required_if:type,paid',
            // 'currency' => 'required_if:type,paid',
            // 'interval' => 'required_if:type,paid',
            // 'trial_period' => 'required_if:type,paid'
        ]);

        $data = $request->all();

        return response($this->planService->create($data, $request->user()), 201);
    }

    /**
     * Update plan by given id
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
            'portal_limit' => 'required',
            'user_limit' => 'required',
            'module_limit' => 'required',
            'is_free_module' => 'required',
            'is_hidden_plan' => 'required',
            // 'price' => 'required_if:type,paid',
            // 'currency' => 'required_if:type,paid',
            // 'interval' => 'required_if:type,paid',
            // 'trial_period' => 'required_if:type,paid'
        ]);

        $data = $request->all();

        return response($this->planService->update($id, $data));
    }

    /**
     * Delete plan by given id
     *
     * @param [type] $id
     * @return void
     */
    public function delete($id)
    {
        return response($this->planService->delete($id));
    }

    /**
     * Search plan
     *
     * @param Request $request
     * @param [type] $keyword
     * @return void
     */
    public function search(Request $request, $keyword)
    {
        $attributes = $request->all();

        $res = $this->planService->search($keyword, $attributes);

        return response($res);
    }
}
