<?php

namespace App\Http\Controllers\Admin;

use App\Models\Interaction;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class InteractionController extends BaseController
{
    public function index(Request $request)
    {
        $query = Interaction::join('interaction_categories as ic', 'ic.id', 'interactions.category_id')
            ->selectRaw('interactions.*, ic.name as category')
            ->withCount('days');

        $columns = ['title', 'description', 'category'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        Interaction::createInstance($request->post());

        return response(['message' => 'Category created'], 200);
    }

    public function update(Request $request, Interaction $interaction)
    {
        $interaction->updateInstance($request->post());

        return response(['message' => 'Category updated'], 200);
    }

    public function destroy(Interaction $interaction)
    {
        $interaction->deleteInstance();

        return response(['message' => 'Interaction deleted'], 200);
    }
}
