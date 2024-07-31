<?php

namespace App\Http\Controllers\Admin;

use App\Models\Media;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class MediaController extends BaseController
{
    public function index(Request $request)
    {
        $query = Media::query();

        $columns = ['id', 'key_name', 'path', 'screen', 'type'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        $values = $request->post('values');

        $exists = Media::where('key_name', $values['key_name'])->exists();
        if ($exists) {
            return response(['message' => 'Media already exists'], 422);
        }

        Media::createInstance($values, $request->post('file'));

        return response(['message' => 'Media created'], 201);
    }

    public function update($mediaId, Request $request)
    {
        $media = Media::findOrFail($mediaId);
        var_dump($media->id);

        $media->updateInstance($request->post('file'));

        return response(['message' => 'Media updated'], 200);
    }

    public function destroy(Media $media)
    {
        if (!$media->is_editable) {
            return response(['message' => 'Media is not editable'], 422);
        }

        $media->deleteInstance();

        return response(['message' => 'Media updated'], 200);
    }
}
