<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramDay;
use App\Models\ProgramWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramWeekController extends Controller
{
    public function index()
    {
        $weeks = ProgramWeek::with(['days' => function($query) {
            $query->orderBy('number', 'asc')->with('interactions')->with('categories');
        }])
        ->with('questions.options')
        ->get();

        $weeks = $weeks->map(function($week) {
            return [
                ...$week->getAttributes(),
                'questions' => $week->questions,
                'is_active' => $week->is_active,
                'days'      => $week->days->map(function($day) {
                    return [
                        ...$day->getAttributes(),
                        'activities' => $day->interactions->merge($day->categories)->mapWithKeys(function($activity) {
                            $type = $activity->pivot->program_day_activity_type === 'App\Models\Interaction' ? 'interaction' : 'category';

                            $arr = $activity->toArray();
                            unset($arr['pivot']);

                            return [$activity->pivot->period => [...$arr, 'type' => $type]];
                        })
                    ];
                })
            ];
        });

        return response($weeks, 200);
    }

    public function store(Request $request)
    {
        $week = ProgramWeek::createInstance($request->post());
        $week->days;

        return response($week, 201);
    }

    public function update(ProgramWeek $week, Request $request)
    {
        $week->description = $request->post('description');
        $week->update();

        return response(['message' => 'Week updated'], 200);
    }

    public function destroy(ProgramWeek $week)
    {
        if ($week->is_active) {
            return response(['message' => 'Week is active'], 400);
        }

        DB::beginTransaction();

        $week->delete();

        ProgramWeek::where('number', '>', $week->number)
            ->orderBy('number', 'asc')
            ->update(['number' => DB::raw('number - 1')]);

        DB::commit();

        $week->deleteInstance();

        return response(['message' => 'Week deleted'], 200);
    }

    public function activate(ProgramWeek $programWeek)
    {
        $isActive = $programWeek->is_active ? 0 : 1;
        $programWeek->update(['is_active' => $isActive]);

        return response(['message' => 'Week active status updated'], 200);
    }
}
