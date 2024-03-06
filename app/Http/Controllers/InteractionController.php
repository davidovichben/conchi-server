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
        $liked = $request->get('liked') ? 1 : 0;

        UserInteraction::where('interaction_id', $interactionId)
            ->where('user_id', Auth::id())
            ->update(['liked' => $liked]);

        return response(['message' => 'Interaction liked'], 200);
    }

    public function setStatus($interactionId)
    {
        $userInteraction = UserInteraction::where('interaction_id', $interactionId)
            ->where('user_id', Auth::id())
            ->first();

        if ($userInteraction->status === 'completed') {
            return response(['message' => 'Status already set'], 400);
        }

        $status = $userInteraction->status === 'initial' ? 'started' : 'completed';
        $userInteraction->update(['status' => $status]);

        return response(['message' => 'Interaction status updated'], 200);
    }
}
