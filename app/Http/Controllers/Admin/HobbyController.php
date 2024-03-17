<?php

namespace App\Http\Controllers\Admin;

use App\Models\Hobby;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class HobbyController extends BaseController
{
    public function index(Request $request)
    {
        $query = Hobby::query();

        $columns = ['name'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        $hobby = Hobby::createInstance($request->post());

        return response($hobby, 201);
    }

    public function update(Request $request, Hobby $hobby)
    {
        $hobby->updateInstance($request->post());

        return response($hobby, 200);
    }

    public function destroy(Hobby $hobby)
    {
        $hobby->delete();

        return response(['message' => 'Hobby deleted'], 200);
    }
}
