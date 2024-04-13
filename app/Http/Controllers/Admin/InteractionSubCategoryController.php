<?php

namespace App\Http\Controllers\Admin;

use App\Models\InteractionSubCategory;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class InteractionSubCategoryController extends BaseController
{
    public function index(Request $request)
    {
        $query = InteractionSubCategory::where('interaction_category_id', $request->post('interactionCategoryId'));

        $columns = ['image', 'name', 'is_personalized'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        $interactionSubCategory = InteractionSubCategory::createInstance($request->post());

        return response($interactionSubCategory, 201);
    }

    public function update(Request $request, InteractionSubCategory $interactionSubCategory)
    {
        $interactionSubCategory->updateInstance($request->post());

        return response($interactionSubCategory, 200);
    }

    public function destroy(InteractionSubCategory $interactionSubCategory)
    {
        $interactionSubCategory->deleteInstance();

        return response(['message' => 'Sub category deleted'], 200);
    }

    public function select(Request $request)
    {
        $subCategories = InteractionSubCategory::select('id', 'name')
            ->where('interaction_category_id', $request->get('categoryId'))
            ->get();

        return response($subCategories, 200);
    }
}
