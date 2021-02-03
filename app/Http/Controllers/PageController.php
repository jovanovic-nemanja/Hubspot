<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PageServices;

class PageController extends Controller
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

        $res = $this->pageService->getAll($attributes);

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
        $res = $this->pageService->getById($id);

        return response($res);
    }

    /**
     * Create new page
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $data = $request->all();

        return response($this->pageService->create($data, $request->user()), 201);
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
        return response($this->pageService->delete($id));
    }
}
