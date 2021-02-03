<?php

namespace App\Http\Controllers;

use App\Services\ModuleServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ModuleController extends Controller
{
	protected $moduleService;

	public function __construct(ModuleServices $moduleService)
	{
		$this->moduleService = $moduleService;
	}

	public function getAll(Request $request, $portalId)
	{
		$attributes = $request->all();

		if (@$attributes['keyword']) {
			$res = $this->moduleService->getAll($attributes, $portalId, true, $request->user());
		} else {
			$res = Cache::remember('getAllModules:' . $portalId, 3600, function () use ($attributes, $portalId, $request) {
				return $this->moduleService->getAll($attributes, $portalId, false, $request->user());
			});
		}

		return response($res);
	}

	public function getAllByCategories(Request $request)
	{
		$attributes = $request->all();

		if (@$attributes['keyword']) {
			$res = $this->moduleService->getAllByCategories($attributes, true);
		} else {
			$res = Cache::rememberForever('getAllModulesByCategories', function () use ($attributes) {
				return $this->moduleService->getAllByCategories($attributes);
			});
		}

		return response($res);
	}

	public function getWebsitePages(Request $request)
	{
		$attributes = $request->all();

		if (@$attributes['keyword']) {
			$res = $this->moduleService->getWebsitePages($attributes, true);
		} else {
			$res = $this->moduleService->getWebsitePages($attributes);
		}

		return response($res);
	}

	public function getLandingPages(Request $request)
	{
		$attributes = $request->all();

		if (@$attributes['keyword']) {
			$res = $this->moduleService->getLandingPages($attributes, true);
		} else {
			$res = $this->moduleService->getLandingPages($attributes);
		}

		return response($res);
	}

	public function getBlogPages(Request $request)
	{
		$attributes = $request->all();

		if (@$attributes['keyword']) {
			$res = $this->moduleService->getBlogPages($attributes, true);
		} else {
			$res = $this->moduleService->getBlogPages($attributes);
		}

		return response($res);
	}

	public function getSystemPages(Request $request)
	{
		$attributes = $request->all();

		if (@$attributes['keyword']) {
			$res = $this->moduleService->getSystemPages($attributes, true);
		} else {
			$res = $this->moduleService->getSystemPages($attributes);
		}

		return response($res);
	}

	public function install(Request $request)
	{
		$attributes = $request->all();

		$res = $this->moduleService->install($attributes, $request->user());

		return response($res);
	}

	public function upgrade(Request $request)
	{
		$attributes = $request->all();

		$res = $this->moduleService->upgrade($attributes, $request->user());

		return response($res);
	}

	public function upgradeInformation(Request $request, $portalId)
	{
		$res = $this->moduleService->upgradeInformation($portalId, $request->user());

		return response($res);
	}

	public function audit(Request $request)
	{
		$res = $this->moduleService->audit($request->user());

		return response($res);
	}
}
