<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\InteractionCategory;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $genders = User::join('user_details', 'users.id', 'user_details.user_id')
            ->selectRaw('SUM(CASE WHEN child_gender = "male" THEN 1 ELSE 0 END) AS male')
            ->selectRaw('SUM(CASE WHEN child_gender = "female" THEN 1 ELSE 0 END) AS female')
            ->first()
            ->toArray();

        $cities = User::selectRaw('cities.name, COUNT("city_id") as total')
            ->join('cities', 'users.city_id', 'cities.id')
            ->groupBy('cities.id')
            ->get();

        $categories = InteractionCategory::leftJoin('interactions as i', 'interaction_categories.id', 'i.category_id')
            ->leftJoin('user_interactions as ui', 'i.id', 'ui.interaction_id')
            ->selectRaw('interaction_categories.name, SUM(CASE WHEN ui.status = "completed" THEN 1 ELSE 0 END) AS completed')
            ->groupBy('interaction_categories.id')
            ->get();

        return response([
            'genders'       => $genders,
            'cities'        => $cities,
            'categories'    => $categories
        ], 200);
    }
}
