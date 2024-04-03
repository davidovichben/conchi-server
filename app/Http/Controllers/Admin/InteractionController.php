<?php

namespace App\Http\Controllers\Admin;

use App\Models\Interaction;
use App\Models\InteractionCategory;
use App\Services\DataTableManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InteractionController extends BaseController
{
    public function index(Request $request)
    {
        $query = Interaction::with('audioFiles')
            ->leftJoin('interaction_categories as ic', 'ic.id', 'interactions.category_id')
            ->leftJoin('interaction_sub_categories as isc', 'isc.id', 'interactions.sub_category_id')
            ->leftJoin('user_interactions as ui', 'ui.interaction_id', 'interactions.id')
            ->selectRaw(
                'interactions.*,
                ic.name as category,
                isc.name as sub_category,
                SUM(liked = 1) AS total_liked,
                ROUND(SUM(liked = 1) / COUNT(*) * 100) AS liked_percentage,
                ROUND(SUM(CASE WHEN status = "initial" THEN 1 ELSE 0 END) / COUNT(*) * 100) AS initial_percentage,
                ROUND(SUM(CASE WHEN status = "started" THEN 1 ELSE 0 END) / COUNT(*) * 100) AS started_percentage,
                ROUND(SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) / COUNT(*) * 100) AS completed_percentage'
            )
            ->groupBy('interactions.id')
            ->withCount('days');

        $columns = [
            'title',
            'show_order',
            'category'              => 'ic.name',
            'sub_category'          => 'isc.name'
        ];

        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        Interaction::createInstance($request->post());

        return response(['message' => 'Interaction created'], 200);
    }

    public function update(Request $request, Interaction $interaction)
    {
        $interaction->updateInstance($request->post());

        return response(['message' => 'Interaction updated'], 200);
    }

    public function destroy(Interaction $interaction)
    {
        $interaction->deleteInstance();

        return response(['message' => 'Interaction deleted'], 200);
    }

    public function select(Request $request)
    {
        $query = Interaction::query();
        if ($request->get('title')) {
            $query->where('title', 'like', '%' . $request->get('title') . '%');
        }

        if ($request->get('category_role') === 'option_sentences') {
            $category = InteractionCategory::where('role', 'option_sentences')->select('id')->first();

            $query->where('category_id', $category->id);
        }

        return response($query->get(), 200);
    }
}
