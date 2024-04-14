<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\InteractionCategory;
use App\Models\InteractionSubCategory;
use App\Models\UserDetail;
use App\Models\UserInteraction;
use App\Models\UserSubCategory;
use App\Services\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserDetailsController extends Controller
{
    public function show()
    {
        $details = UserDetail::where('user_id', Auth::id())->first();

        $values = [...$details->toArray()];

        foreach (['name', 'nickname'] as $value) {
            $file = Auth::user()->getFile($value, 'webm');
            if ($file) {
                $values['recorded_' . $value] = $file;
            }
        }

        return response($values, 200);
    }

    public function update(Request $request)
    {
        $familyStatuses = collect(config('constants.family_status'))->pluck('id');
        $childPositions = collect(config('constants.child_position'))->pluck('id');

        $validated = $request->validate([
            'family_status'         => [Rule::in($familyStatuses)],
            'parent1_name'          => 'max:50',
            'parent1_role'          => [Rule::in(['father', 'mother'])],
            'parent2_name'          => 'max:50',
            'parent2_role'          => [Rule::in(['father', 'mother'])],
            'child_gender'          => [Rule::in(['male', 'female'])],
            'child_birth_date'      => 'date',
            'child_name'            => 'max:50',
            'child_has_nickname'    => 'boolean',
            'child_nickname'        => 'max:50',
            'child_position'        => [Rule::in($childPositions)],
        ]);

        foreach (['name', 'nickname'] as $value) {
            $file = $request->post('recorded_' . $value);
            if ($file) {
                (new UploadedFile($file))->store(Auth::user()->getPath() . '/' . $value, 'webm');
            }
        }

        UserDetail::where('user_id', Auth::id())->update($validated);

        return response(['message' => 'Details updated'], 200);
    }


    public function updateSubCategories(InteractionCategory $category, Request $request)
    {
        if ($category->personalization_limit && $category->personalization_limit < $request->collect('subCategoriesIds')->count()) {
            return response(['message' => 'You can select maximum ' . $category->personalization_limit . ' sub categories'], 400);
        }

        $subCategories = InteractionSubCategory::whereIn('id', $request->collect('subCategoriesIds'))
            ->select('id')
            ->get();

        $subCategoriesIds = $category->subCategories->pluck('id')->toArray();

        UserSubCategory::where('user_id', Auth::id())->whereIn('interaction_sub_category_id', $subCategoriesIds)->delete();

        $insertValues = $subCategories->map(function($subCategory) {
            return ['user_id' => Auth::id(), 'interaction_sub_category_id' => $subCategory->id];
        });

        UserSubCategory::insert($insertValues->toArray());

        DB::commit();

        return response(['message' => 'Sub categories updated'], 200);
    }

    public function updateInteractions(InteractionCategory $category, Request $request)
    {
        if ($category->personalization_limit && $category->personalization_limit < $request->collect('interactionIds')->count()) {
            return response(['message' => 'You can select maximum ' . $category->personalization_limit . ' interactions'], 400);
        }

        DB::beginTransaction();

        $interactionIds = $category->interactions->pluck('id')->toArray();

        UserInteraction::where('user_id', Auth::id())->whereIn('interaction_id', $interactionIds)->update(['selected' => 0]);

        UserInteraction::upsert(['selected' => 1],
            where: ['user_id' => Auth::id()],
            whereIn: ['interaction_id', $interactionIds],
            uniqueBy: ['user_id', 'interaction_id'],
            update: ['selected']);

        DB::commit();

        return response(['message' => 'Interactions updated'], 200);
    }
}
