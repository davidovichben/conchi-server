<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class PageController extends BaseController
{

    public function index(Request $request)
    {
        $query = Page::query();

        $columns = ['title', 'description', 'type'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function update(Request $request, Page $page)
    {
        $page->updateInstance($request->post());

        return response($page, 200);
    }
}
