<?php

namespace App\Http\Controllers;

use App\Models\InteractionCategory;
use App\Models\InteractionSubCategory;
use App\Models\Translation;
use App\Models\UserDetail;
use App\Models\UserSubCategory;
use App\Models\UserSentence;
use App\Services\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserDetailsController extends Controller
{
    public function show()
    {
        $details = UserDetail::where('user_id', Auth::id())->first();
        $hobbies = UserSubCategory::where('user_id', Auth::id())->get()->pluck('interaction_sub_category_id');
        $sentences = UserSentence::where('user_id', Auth::id())->get()->pluck('sentence_id');

        $values = [
            ...$details->toArray(),
            'hobbies'   => $hobbies,
            'sentences' => $sentences,
        ];

        foreach (['name', 'nickname'] as $value) {
            $file = 'users/' . Auth::id() . '/' . $value . '.webm';
            if (Storage::exists($file)) {
                $values['recorded_' . $value] = 'data:audio/webm;codecs=opus;base64,' . base64_encode(Storage::get($file));
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
                (new UploadedFile($file))->store('users/' . Auth::id() . '/' . $value, 'webm');
            }
        }

        UserDetail::where('user_id', Auth::id())->update($validated);

        return response(['message' => 'Details updated'], 200);
    }

    public function updateSubCategories(Request $request)
    {
        $subCategories = InteractionSubCategory::whereIn('id', $request->collect('subCategories'))
            ->select('id')
            ->get();

        UserSubCategory::where('user_id', Auth::id())->delete();

        $insertValues = $subCategories->map(function($subCategory) {
            return ['user_id' => Auth::id(), 'interaction_sub_category_id' => $subCategory->id];
        });

        UserSubCategory::insert($insertValues->toArray());

        DB::commit();

        return response(['message' => 'Sub categories updated'], 200);
    }

    public function updateSentences(Request $request)
    {
        DB::beginTransaction();

        UserSentence::where('user_id', Auth::id())->delete();

        $category = InteractionCategory::where('role', 'power_sentences')->with(['interactions' => function ($query) use ($request) {
            $query->select('id', 'category_id')->whereIn('id', $request->collect('sentences'));
        }])->first();

        $insertValues = $category->interactions->map(function($interaction) {
            return ['user_id' => Auth::id(), 'sentence_id' => $interaction->id];
        });

        UserSentence::insert($insertValues->toArray());

        DB::commit();

        return response(['message' => 'Sentences updated'], 200);
    }
}
