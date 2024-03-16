<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramWeek;

class ProgramWeekController extends Controller
{
    public function index()
    {
        $weeks = ProgramWeek::with(['days' => function($query) {
            $query->orderBy('number', 'asc')->with('interactions');
        }])->get();

        $weeks = $weeks->map(function($week) {
            return [
                ...$week->getAttributes(),
                'days' => $week->days->map(function($day) {
                    return [
                        ...$day->getAttributes(),
                        'interactions' => $day->interactions->mapWithKeys(function($interaction) {
                            return [$interaction->pivot->period => $interaction];
                        })
                    ];
                })
            ];
        });

        return response($weeks, 200);
    }

    public function store()
    {

    }

    public function destroy()
    {

    }
}
