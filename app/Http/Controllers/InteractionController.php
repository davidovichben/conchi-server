<?php

namespace App\Http\Controllers;

use App\Models\Interaction;

use App\Models\InteractionCategory;
use App\Models\UserInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InteractionController extends Controller
{
    public function categories()
    {
        $categories = InteractionCategory::all();

        return response($categories, 200);
    }

    public function byCategory(InteractionCategory $category)
    {
        $query = Interaction::getQuery(Auth::id());

        $rows = $query->where('category_id', $category->id)->get();

        $category->interactions = Interaction::getInteractions($rows);

        return response($category, 200);
    }

    public function like($interactionId, Request $request)
    {
        $values = [
            'liked'             => $request->get('liked') ? 1 : 0,
            'user_id'           => Auth::id(),
            'interaction_id'    => $interactionId
        ];

        UserInteraction::upsertInstance($values);

        return response(['message' => 'Interaction liked'], 200);
    }

    public function setStatus($interactionId)
    {
        $userInteraction = UserInteraction::where('interaction_id', $interactionId)
            ->where('user_id', Auth::id())
            ->first();

        if ($userInteraction && $userInteraction->status === 'completed') {
            return response(['message' => 'Status already set'], 400);
        }

        $values = [
            'status'            => !$userInteraction || $userInteraction->status === 'initial' ? 'started' : 'completed',
            'user_id'           => Auth::id(),
            'interaction_id'    => $interactionId
        ];

        UserInteraction::upsertInstance($values);

        return response(['message' => 'Interaction status updated'], 200);
    }
}
