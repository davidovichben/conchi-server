<?php

namespace App\Http\Controllers\Admin;

use App\Models\InteractionCategory;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class InteractionCategoryController extends BaseController
{
    public function index(Request $request)
    {
        $query = InteractionCategory::withCount('interactions');

        $columns = ['name', 'description', 'interactions_count', 'personalization_limit', 'should_display'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function show(InteractionCategory $interactionCategory)
    {
        return response($interactionCategory, 200);
    }

    public function store(Request $request)
    {
        InteractionCategory::createInstance($request->post());

        return response(['message' => 'Category created'], 200);
    }

    public function update(Request $request, InteractionCategory $interactionCategory)
    {
        $interactionCategory->updateInstance($request->post());

        return response(['message' => 'Category updated'], 200);
    }
    public function destroy(InteractionCategory $interactionCategory)
    {
        if ($interactionCategory->role) {
            return response(['message' => 'You can not delete this category'], 400);
        }

        $interactionCategory->deleteInstance();

        return response(['message' => 'Category deleted'], 200);
    }

    public function select()
    {
        $categories = InteractionCategory::select('id', 'name', 'image')->get();
        return response($categories, 200);
    }
}
