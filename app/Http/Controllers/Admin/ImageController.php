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

        $columns = ['id', 'key_name', 'path', 'screen'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        Image::createInstance($request->post('values'), $request->post('file'));

        return response(['message' => 'Image created'], 201);
    }

    public function update(Image $image, Request $request)
    {
        $image->updateInstance($request->post('file'));

        return response(['message' => 'Image updated'], 200);
    }

    public function destroy(Image $image)
    {
        if (!$image->is_editable) {
            return response(['message' => 'Image is not editable'], 422);
        }

        $image->deleteInstance();

        return response(['message' => 'Image updated'], 200);
    }
}
