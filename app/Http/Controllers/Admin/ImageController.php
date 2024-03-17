<?php

namespace App\Http\Controllers\Admin;

use App\Models\Image;
use App\Services\DataTableManager;
use App\Services\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageController extends BaseController
{
    public function index(Request $request)
    {
        $query = Image::query();

        $columns = ['id', 'key_name', 'path'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function update(Image $image, Request $request)
    {
        $image->updateInstance($request->post('file'));

        return response(['message' => 'Image updated'], 200);
    }
}
