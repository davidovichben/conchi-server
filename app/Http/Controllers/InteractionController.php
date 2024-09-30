<?php

namespace App\Http\Controllers;

use App\Models\Interaction;

use App\Models\InteractionCategory;
use App\Models\InteractionSubCategory;
use App\Models\UserInteraction;
use App\Models\UserSubCategory;
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
                'image' => $category->image ? url(Storage::url($category->image)) : null
            ];
        });

        return response($categories, 200);
    }

    public function personalizedCategories()
    {
        $storageUrl = rtrim(Storage::url('/c'), 'c');
        var_dump($storageUrl);
        $categories = InteractionCategory::where('is_personalized', 1)
            ->selectRaw('id, name, personalization_limit, should_display')
            ->with(['subCategories' => function($query) {
                $query->selectRaw('id, interaction_category_id, name, image, usc.user_id as "selected"')
                    ->leftJoin('user_sub_categories as usc', function($query) {
                        $query->on('usc.interaction_sub_category_id', 'interaction_sub_categories.id')
                            ->where('usc.user_id', Auth::id());
                    });
            }])
            ->with(['interactions' => function($query) {
                $query->selectRaw('interactions.id, category_id, title, ui.user_id as "selected"')
                    ->leftJoin('user_interactions as ui', function($query) {
                        $query->on('ui.interaction_id', 'interactions.id')
                            ->where('ui.user_id', Auth::id())
                            ->where('ui.selected', 1);
                    });
            }])
            ->get();

        return response($categories, 200);
    }

    public function subCategories(InteractionCategory $interactionCategory)
    {
        $subCategories = $interactionCategory->subCategories->map(function ($subCategory) {
            return [
                ...$subCategory->toArray(),
                'image' => $subCategory->image ? url(Storage::url($subCategory->image)) : null
            ];
        });

        if ($interactionCategory->is_personalized) {
            $userSubCategories = UserSubCategory::where('user_id', Auth::id())
                ->whereIn('interaction_sub_category_id', $subCategories->pluck('id')->toArray())
                ->select('interaction_sub_category_id')
                ->get();

            $subCategories = $subCategories->filter(function ($subCategory) use ($userSubCategories) {
                return $userSubCategories->contains('interaction_sub_category_id', $subCategory['id']);
            })->values();
        }

        return response([
            'name'              => $interactionCategory->name,
            'sub_categories'    => $subCategories
        ], 200);
    }

    public function byCategory(InteractionCategory $interactionCategory)
    {
        return $this->getByCategory($interactionCategory);
    }

    public function byCategoryRole(Request $request)
    {
        $role = $request->get('role');
        $interactionCategory = InteractionCategory::where('role', $role)->firstOrFail();

        return $this->getByCategory($interactionCategory);
    }


    public function bySubCategory(InteractionSubCategory $interactionSubCategory)
    {
        $interactions = Interaction::where('sub_category_id', $interactionSubCategory->id)
            ->with('audioFiles')
            ->with(['userInteractions' => function($query) {
                $query->where('user_id', Auth::id());
            }])
            ->orderBy('show_order', 'asc')
            ->get();


        $prefixFiles = Auth::user()->getPrefixFiles();

        $interactions = Interaction::mapInteractions($interactions, Auth::user(), $prefixFiles, false);
        return response([...$interactionSubCategory->toArray(), 'interactions' => $interactions], 200);
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

    private function getByCategory($interactionCategory)
    {
        $interactions = Interaction::where('category_id', $interactionCategory->id)
            ->with('audioFiles')
            ->with('category')
            ->with(['userInteractions' => function($query) {
                $query->where('user_id', Auth::id());
            }])
            ->orderBy('show_order', 'asc')
            ->get();


        if ($interactionCategory->role === 'option_sentences' || $interactionCategory->is_personalized) {
            $userInteractions = UserInteraction::where('user_id', Auth::id())
                ->whereIn('interaction_id', $interactions->pluck('id')->toArray())
                ->where('selected', 1)
                ->select('interaction_id')
                ->get();

            $interactions = $interactions->filter(function ($interaction) use ($userInteractions) {
                return $userInteractions->contains('interaction_id', $interaction['id']);
            })->values();
        }

        $prefixFiles = Auth::user()->getPrefixFiles();

        $interactions = Interaction::mapInteractions($interactions, Auth::user(), $prefixFiles);

        return response([...$interactionCategory->toArray(), 'interactions' => $interactions], 200);
    }
}
