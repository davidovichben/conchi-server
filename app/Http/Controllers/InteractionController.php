<?php

namespace App\Http\Controllers;

use App\Models\Interaction;

use App\Models\InteractionCategory;
use App\Models\InteractionSubCategory;
use App\Models\UserInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InteractionController extends Controller
{
    public function categories()
    {
        $categories = InteractionCategory::all()->map(function ($category) {
            return [
                ...$category->toArray(),
                'image' => url(Storage::url($category->image))
            ];
        });

        return response($categories, 200);
    }

    public function byCategory(InteractionCategory $interactionCategory)
    {
        $interactions = Interaction::where('category_id', $interactionCategory->id)
            ->with('audioFiles')
            ->with('category')
            ->with(['userInteractions' => function($query) {
                $query->where('user_id', Auth::id());
            }])
            ->orderBy('show_order', 'asc')
            ->get();


        $interactions = Interaction::mapInteractions($interactions, Auth::user());

        return response([...$interactionCategory->toArray(), 'interactions' => $interactions], 200);
    }

    public function bySubCategory($subCategoryId)
    {
        $interactions = Interaction::where('sub_category_id', $subCategoryId)
            ->with('audioFiles')
            ->with('subCategory')
            ->with(['userInteractions' => function($query) {
                $query->where('user_id', Auth::id());
            }])
            ->orderBy('show_order', 'asc')
            ->get();


        $interactions = Interaction::mapInteractions($interactions, Auth::user(), false);
        return response($interactions, 200);
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
