<?php

namespace App\Http\Controllers\Admin;

use App\Models\Image;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class ImageController extends BaseController
{
    public function index(Request $request)
    {
        $query = Image::selectRaw('*, CONCAT("images/", file_name) as image');

        $columns = ['id', 'key_name', 'file_name', 'image'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function update(Image $image, Request $request)
    {
        return response(['message' => 'Image updated'], 200);
    }
}
