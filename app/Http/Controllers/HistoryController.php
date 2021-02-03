<?php

namespace App\Http\Controllers;

use Str;
use Carbon\Carbon;
use App\Models\Modules;
use Illuminate\Http\Request;
use App\Services\PageServices;
use App\Models\History;
use App\Models\HistoryModules;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
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
        $oldhistories = History::where("created_at", "<", Carbon::now()->subMonths(2))->get();
        if(@$oldhistories) {
            foreach($oldhistories as $old) {
                $oldh = DB::table('histories_modules')->where('history_id', $old['id'])->delete();
            }
        }
        $oldhistories = History::where("created_at", "<", Carbon::now()->subMonths(2))->delete();

        $attributes = $request->all();
        $userid = auth()->id();
        $agencies_user = DB::table('agencies_users')->where('agencies_users.user_id', $userid)->select('agencies_users.agency_id')->get();
        $agencies_user_id = $agencies_user[0]->agency_id;

        if(@$attributes['showmypage']) {
            if(@$attributes['keyword']) {
                $res = DB::table('histories')->Join('users', 'users.id', '=', 'histories.user_id')
                                            ->where('histories.user_id', $userid)
                                            ->where('histories.name', 'like', '%' . $attributes['keyword'] . '%')
                                            ->whereNull('histories.deleted_at')
                                            ->take(20)
                                            ->select('histories.*', 'users.name as username')
                                            ->get();
                // $res = History::where('user_id', $userid)->where('name', 'like', '%' . $attributes['keyword'] . '%')->whereNull('deleted_at')->take(20)->get();
            }else{
                $res = DB::table('histories')->Join('users', 'users.id', '=', 'histories.user_id')
                                            ->where('histories.user_id', $userid)
                                            ->whereNull('histories.deleted_at')
                                            ->take(20)
                                            ->select('histories.*', 'users.name as username')
                                            ->get();
                // $res = History::where('user_id', $userid)->whereNull('deleted_at')->take(20)->get();
            }
        }else{
            if(@$attributes['keyword']) {
                $res = DB::table('histories')->Join('users', 'users.id', '=', 'histories.user_id')
                                            ->where('histories.name', 'like', '%' . $attributes['keyword'] . '%')
                                            ->whereNull('histories.deleted_at')
                                            ->where('histories.agency_id', $agencies_user_id)
                                            ->take(20)
                                            ->select('histories.*', 'users.name as username')
                                            ->get();
                // $res = History::where('agency_id', $agencies_user_id)->where('name', 'like', '%' . $attributes['keyword'] . '%')->whereNull('deleted_at')->take(20)->get();
            }else{
                $res = DB::table('histories')->Join('users', 'users.id', '=', 'histories.user_id')
                                            ->whereNull('histories.deleted_at')
                                            ->where('histories.agency_id', $agencies_user_id)
                                            ->take(20)
                                            ->select('histories.*', 'users.name as username')
                                            ->get();
                // $res = History::where('agency_id', $agencies_user_id)->whereNull('deleted_at')->take(20)->get();
            }
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

        $result['histories'] = History::where('id', $id)->first();

        $result['modules'] = DB::table('histories_modules')
                            ->Join('modules', 'histories_modules.modules', '=', 'modules.name')
                            ->where('histories_modules.history_id', $id)
                            ->whereNull('histories_modules.deleted_at')
                            ->orderBy('histories_modules.id', 'asc')
                            ->select('modules.*')
                            ->get();

        return response($result);
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

        return response($this->pageService->createhistory($data, $request->user()), 201);
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
        HistoryModules::where('history_id', $id)->delete();
        return response(History::where('id', $id)->delete());
    }
}
