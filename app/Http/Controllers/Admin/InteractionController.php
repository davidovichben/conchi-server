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
            ->selectRaw('interactions.id, interactions.title, interactions.description, ic.name as category');


        $columns = ['name', 'description', 'category'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Interaction $interaction)
    {
        //
    }

    public function update(Request $request, Interaction $interaction)
    {
        //
    }

    public function destroy(Interaction $interaction)
    {
        //
    }
}
