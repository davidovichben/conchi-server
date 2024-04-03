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

        $user = Auth::user()->load('details')->load('subCategories')->load('sentences');

        $prefixFiles = $user->getPrefixFiles();

        $programDay->load('interactions')
            ->load(['categories' => function ($query) use ($user) {
                $query->with(['subCategories' => function ($query) use ($user) {
                    $query->whereIn('id', $user->subCategories->pluck('id')->toArray());
                }])->with(['interactions' => function ($query) use ($user) {
                    $query->orderBy('show_order', 'asc')->whereIn('id', $user->sentences->pluck('id')->toArray());
                }]);
        }]);

        $programDay->interactions->load('audioFiles')
            ->load('category')
            ->load(['userInteractions' => function($query) {
                $query->where('user_id', Auth::id());
            }]);

        $categories = $programDay->categories->mapWithKeys(function ($category) use ($prefixFiles) {
            $values = [
                'name'  => $category->name,
                'image' => $category->image ? url(Storage::url($category->image)) : null
            ];

            if ($category->should_display === 'interactions') {
                $values['interactions'] = Interaction::mapInteractions($category->interactions, Auth::user(), $prefixFiles);
            } else {
                $values['subCategories'] = $category->subCategories->map(function ($subCategory) {
                    return [
                        'name'  => $subCategory->name,
                        'image' => $subCategory->image ? url(Storage::url($subCategory->image)) : null
                    ];
                });
            }

            return [$category->pivot->period => $values];
        });

        $interactions = $programDay->interactions->mapWithKeys(function ($interaction) {
            return [$interaction->pivot->period => $interaction];
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

            $interactions = $options->pluck('interaction');

            $categories['afternoon'] = [
                'interactions' => Interaction::mapInteractions($interactions, $user, $prefixFiles)
            ];
        }

        $interactions = Interaction::mapInteractions($interactions, $user, $prefixFiles);

        $nextDay = $programDay->nextDay();

//        var_dump(count(DB::getQueryLog()));

        return response([
            'weekId'        => $programDay->week_id,
            'nextDayId'     => $nextDay ? $nextDay->id : null,
            'interactions'  => $interactions,
            'categories'    => $categories
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

        $weekDaysCount = ProgramDay::where('week_id', $programDay->week_id)->count();
        $completedDaysCount = UserProgramDay::where('completed', 1)->where('user_id', Auth::id())->count();

        if ($weekDaysCount === $completedDaysCount) {
            $week = ProgramWeek::where('id', $programDay->week_id)->first();

            UserProgramWeek::where('user_id', Auth::id())
                ->where('program_week_id', $programDay->week_id)
                ->update(['status' => 'completed']);


            UserProgramWeek::where('user_id', Auth::id())
                ->where('program_week_id', $week->nextWeek()->id)
                ->update(['status' => 'active']);
        }

        DB::commit();

        return response(['message' => 'Day completed'], 200);
    }
}
