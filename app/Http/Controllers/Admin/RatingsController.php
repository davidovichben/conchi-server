<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rating;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class RatingsController extends BaseController
{
    public function index(Request $request)
    {
        $query = Rating::query();

        $columns = ['path', 'type', 'content', 'name'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        $rating = Rating::createInstance($request->post(), $request->post('image'));

        return response($rating, 201);
    }

    public function update(Request $request, Rating $rating)
    {
        $rating->updateInstance($request->post(), $request->post('image'));

        return response($rating, 200);
    }

    public function destroy(Rating $rating)
    {
        $rating->delete();

        return response(['message' => 'Rating deleted'], 200);
    }
}
