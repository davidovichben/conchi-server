<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContentPackage;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class ContentPackageController extends BaseController
{
    public function index(Request $request)
    {
        $query = ContentPackage::query();

        $columns = ['title', 'price', 'description'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        $contentPackage = ContentPackage::createInstance($request->post());

        return response($contentPackage, 201);
    }

    public function update(Request $request, ContentPackage $contentPackage)
    {
        $contentPackage->updateInstance($request->post());

        return response($contentPackage, 200);
    }

    public function destroy(ContentPackage $contentPackage)
    {
        $contentPackage->delete();

        return response(['message' => 'Content package deleted'], 200);
    }
}
