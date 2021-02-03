<?php

namespace App\Http\Controllers;

use Str;
use App\Models\Modules;
use Illuminate\Http\Request;
use App\Services\PageServices;
use App\Models\Layouts;
use App\Models\LayoutsModules;
use Illuminate\Support\Facades\DB;

class LayoutsController extends Controller
{
    protected $pageService;

    public function __construct(PageServices $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * Get all pages
     *
     * @param Request $request
     * @return void
     */
    public function getAll(Request $request)
    {
        $attributes = $request->all();
        $userid = auth()->id();
        $agencies_user = DB::table('agencies_users')->where('agencies_users.user_id', $userid)->select('agencies_users.agency_id')->get();
        $agencies_user_id = $agencies_user[0]->agency_id;

        if(@$attributes['keyword']) {
            $res = Layouts::where('agency_id', $agencies_user_id)->where('name', 'like', '%' . $attributes['keyword'] . '%')->whereNull('deleted_at')->get();
        }else{
            $res = Layouts::where('agency_id', $agencies_user_id)->whereNull('deleted_at')->get();
        }
        
        return response($res);
    }

    /**
     * Get pages by given id
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function getById(Request $request, $id)
    {
        $result = [];

        $result['layouts'] = Layouts::where('id', $id)->first();

        $result['modules'] = DB::table('layouts_modules')
                            ->Join('modules', 'layouts_modules.modules', '=', 'modules.name')
                            ->where('layouts_modules.layout_id', $id)
                            ->whereNull('layouts_modules.deleted_at')
                            ->orderBy('layouts_modules.id', 'asc')
                            ->select('modules.*')
                            ->get();

        return response($result);
    }

    protected function dataWrapper($data)
    {
        $results = [];

        if (isset($data['data'])) {
            $results['data'] = $data['data'];

            unset($data['data']);

            $results['meta'] = $data;
        } else {
            $results['data'] = $data;
        }

        return $results;
    }

    /**
     * Create new layout
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $data = $request->all();

        return response($this->pageService->createlayout($data, $request->user()), 201);
    }

    /**
     * Create new custom page
     *
     * @param Request $request
     * @return void
     */
    public function createCustomPage(Request $request)
    {
        $data = $request->all();

        return response($this->pageService->createCustomPage($data, $request->user()), 201);
    }

    /**
     * Update page by given id
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        return response($this->pageService->update($id, $data));
    }

    /**
     * Delete page by given id
     *
     * @param [type] $id
     * @return void
     */
    public function delete($id)
    {
        LayoutsModules::where('layout_id', $id)->delete();
        return response(Layouts::where('id', $id)->delete());
    }
}
