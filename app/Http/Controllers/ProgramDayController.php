<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\ProgramDay;
use App\Models\ProgramReportOption;
use App\Models\ProgramReportQuestion;
use App\Models\ProgramWeek;
use App\Models\UserProgramDay;
use App\Models\UserProgramReport;
use App\Models\UserProgramWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProgramDayController extends Controller
{
    public function show(ProgramDay $programDay)
    {
//        DB::enableQueryLog();

        $user = Auth::user()->load('details')->load('subCategories')->load(['interactions' => function($query) {
            $query->where('selected', 1);
        }]);

        $prefixFiles = $user->getPrefixFiles();

        $programDay->load('interactions')
            ->load(['categories' => function ($query) use ($user) {
                $query->with(['subCategories' => function ($query) use ($user) {
                    $query->whereIn('id', $user->subCategories->pluck('id')->toArray());
//                    $query->join('interaction_categories as uc', 'interaction_sub_categories.category_id', 'uc.id')
//                        ->whereIn('interaction_sub_categories.id', $user->subCategories->pluck('id')->toArray());
                }])->with(['interactions' => function ($query) use ($user) {
                    $query->orderBy('show_order', 'asc');//->whereIn('id', $user->interactions->pluck('id')->toArray());
                }]);
            }]);

        $programDay->interactions->load('audioFiles')
            ->load('category')
            ->load(['userInteractions' => function($query) {
                $query->where('user_id', Auth::id());
            }]);

        $categories = $programDay->categories->mapWithKeys(function ($category) use ($prefixFiles, $user) {
            $values = [
                'id'    => $category->id,
                'name'  => $category->name,
                'image' => $category->image ? url(Storage::url($category->image)) : null
            ];

            if ($category->should_display === 'interactions') {
                $interactions = $category->is_personalized ? $category->interactions->filter(function($interaction) use ($user) {
                    return $user->interactions->contains('id', $interaction->id);
                }) : $category->interactions;

                $values['interactions'] = Interaction::mapInteractions($interactions, Auth::user(), $prefixFiles);
            } else {
                $subCategories = $category->is_personalized ? $category->subCategories->filter(function($subCategory) use ($user) {
                    return $user->subCategories->contains('id', $subCategory->id);
                }) : $category->subCategories;

                $values['sub_categories'] = $subCategories->map(function ($subCategory) {
                    return [
                        'id'    => $subCategory->id,
                        'name'  => $subCategory->name,
                        'image' => $subCategory->image ? url(Storage::url($subCategory->image)) : null
                    ];
                });
            }

            return [$category->pivot->period => $values];
        });


        if ($programDay->number === 2 && $programDay->week->number > 1) {
            $userOptions = UserProgramReport::where('user_id', Auth::id())->select('program_report_option_id')
                ->get()
                ->pluck('program_report_option_id')->toArray();

            $previousWeek = $programDay->week->previousWeek();

            $questionIds = ProgramReportQuestion::where('program_week_id', $previousWeek->id)->select('id')->get()->pluck('id');

            $options = ProgramReportOption::whereIn('program_report_question_id', $questionIds)
                ->whereIn('id', $userOptions)
                ->with('interaction')
                ->get();

            $afternoonInteractions = $options->pluck('interaction');

            $categories['afternoon'] = [
                'interactions' => Interaction::mapInteractions($afternoonInteractions, $user, $prefixFiles)
            ];
        }

        $interactions = $programDay->interactions->mapWithKeys(function ($interaction) use ($user, $prefixFiles) {
            $mapped = Interaction::mapInteraction($interaction, $user, $prefixFiles);
            return [$interaction->pivot->period => $mapped];
        });

        $nextDay = $programDay->nextDay();

        $userProgramDay = UserProgramDay::where('program_day_id', $programDay->id)
            ->where('user_id', Auth::id())
            ->first();

//        var_dump(count(DB::getQueryLog()));

        return response([
            'number'        => $programDay->number,
            'weekId'        => $programDay->week_id,
            'isLastWeek'    => $programDay->week->nextWeek() === null,
            'nextDayId'     => $nextDay ? $nextDay->id : null,
            'interactions'  => $interactions,
            'categories'    => $categories,
            'completed'     => $userProgramDay ? $userProgramDay->completed : false,
            'labels'        => [
                'morning'   => $programDay->morning_label,
                'afternoon' => $programDay->afternoon_label,
                'evening'   => $programDay->evening_label,
                'night'     => $programDay->night_label
            ]
        ], 200);
    }

    public function complete(ProgramDay $programDay)
    {
        $userProgramDay = UserProgramDay::where('program_day_id', $programDay->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($userProgramDay && $userProgramDay->completed) {
            return response(['message' => 'Bad request'], 400);
        }

        $values = [
            'completed'         => 1,
            'user_id'           => Auth::id(),
            'program_day_id'    => $programDay->id
        ];

        DB::beginTransaction();

        UserProgramDay::createInstance($values);

        $weekDays = ProgramDay::where('week_id', $programDay->week_id)->select('id')->get();
        $completedDaysCount = UserProgramDay::where('completed', 1)
            ->whereIn('program_day_id', $weekDays->pluck('id'))
            ->where('user_id', Auth::id())
            ->count();

        if ($weekDays->count() === $completedDaysCount) {
            $week = ProgramWeek::where('id', $programDay->week_id)->first();

            UserProgramWeek::where('user_id', Auth::id())
                ->where('program_week_id', $programDay->week_id)
                ->update(['status' => 'completed']);

            if ($week->nextWeek()) {
                $values = [
                    'user_id'           => Auth::id(),
                    'program_week_id'   => $week->nextWeek()->id,
                    'status'            => 'active'
                ];

                UserProgramWeek::upsert($values,
                    uniqueBy: ['user_id', 'program_week_id'],
                    update: ['status']);
            }
        }

        DB::commit();

        return response(['message' => 'Day completed'], 200);
    }
}
