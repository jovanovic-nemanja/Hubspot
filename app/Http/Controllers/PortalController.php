<?php

namespace App\Http\Controllers;

use App\Services\PortalServices;
use Illuminate\Http\Request;

class PortalController extends Controller
{
	protected $portalService;

	public function __construct(PortalServices $portalService)
	{
		$this->portalService = $portalService;
	}

	/**
	 * Get all portals
	 *
	 * @param Request $request
	 * @return void
	 */
	public function getAll(Request $request)
	{
		$attributes = $request->all();

		$res = $this->portalService->getAll($attributes, $request->user());

		return response($res);
	}

	/**
	 * Get portal by given id
	 *
	 * @param Request $request
	 * @param [type] $id
	 * @return void
	 */
	public function getById(Request $request, $id)
	{
		$attributes = $request->all();

		$res = $this->portalService->getById($id, $attributes, $request->user());

		return response($res);
	}

	/**
	 * Get portal module list
	 *
	 * @param Request $request
	 * @param [type] $id
	 * @return void
	 */
	public function getModulesById(Request $request, $id)
	{
		$res = $this->portalService->getModulesById($id, $request->user());

		r($res);
	}

	/**
	 * Get portal page list
	 *
	 * @param Request $request
	 * @param [type] $id
	 * @return void
	 */
	public function getPagesById(Request $request, $id)
	{
		$res = $this->portalService->getPagesById($id, $request->user());

		r($res);
	}

	/**
	 * Get portal folder list
	 *
	 * @param Request $request
	 * @param [type] $id
	 * @return void
	 */
	public function getTemplatesById(Request $request, $id)
	{
		$res = $this->portalService->getTemplatesById($id, $request->user());

		r($res);
	}

	/**
	 * Check portal exists
	 *
	 * @param  Request
	 * @param  [type] $hub_id
	 * @return [type]
	 */
	public function checkPortalExists(Request $request, $hub_id)
	{
		$res = $this->portalService->checkPortalExists($hub_id);

		return response($res);
	}

	/**
	 * Create new portal
	 *
	 * @param Request $request
	 * @return void
	 */
	public function create(Request $request)
	{
		$data = $request->all();

		return response($this->portalService->create($data, $request->user()), 201);
	}

	/**
	 * Setup sr wizard
	 *
	 * @param Request $request
	 * @return void
	 */
	public function setupWizard(Request $request)
	{
		$data = $request->all();

		return response($this->portalService->setupWizard($data, $request->user()));
	}

	/**
	 * Update sr wizard
	 *
	 * @param Request $request
	 * @return void
	 */
	public function updateWizard(Request $request)
	{
		$data = $request->all();

		return response($this->portalService->updateWizard($data, $request->user()));
	}

	/**
	 * Update portal by given id
	 *
	 * @param Request $request
	 * @param [type] $id
	 * @return void
	 */
	public function update(Request $request, $id)
	{
		$data = $request->all();

		return response($this->portalService->update($id, $data));
	}

	/**
	 * Delete portal by given id
	 *
	 * @param [type] $id
	 * @return void
	 */
	public function delete($id)
	{
		return response($this->portalService->delete($id));
	}

	public function checkPortalAccess(Request $request, $id)
	{
		return response($this->portalService->checkPortalAccess($id, $request->user()));
	}
}
