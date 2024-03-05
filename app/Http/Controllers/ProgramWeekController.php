<?php

namespace App\Http\Controllers;

use App\Models\ProgramDay;
use App\Models\ProgramWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramWeekController extends Controller
{
    public function index()
    {
        $weeks = ProgramWeek::with(['userWeeks' => function ($query) {
            return $query->where('user_id', Auth::id());
        }])->get();

        $mapped = $weeks->mapWithKeys(function($week) {
            return [
                $week->id => [
                    'id'            => $week['id'],
                    'description'   => $week['description'],
                    'status'        => $week['userWeeks'][0]['status']
                ]
            ];
        });

        return response($mapped->values(), 200);
    }
    public function days($weekid)
    {
        $days = ProgramDay::where('week_id', $weekid)->with(['userDays' => function ($query) {
            return $query->where('user_id', Auth::id());
        }])->get();

        $mapped = $days->mapWithKeys(function($day) {
            return [
                $day->id => [
                    'id'        => $day['id'],
                    'completed' => $day['userDays'][0]['completed']
                ]
            ];
        });

        return response($mapped->values(), 200);
    }
}
