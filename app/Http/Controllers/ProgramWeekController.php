<?php

namespace App\Http\Controllers;

use App\Models\ProgramDay;
use App\Models\ProgramWeek;
use Illuminate\Http\Request;

class ProgramWeekController extends Controller
{
    public function index()
    {
        $weeks = ProgramWeek::all();

        return response($weeks, 200);
    }
    public function days(Request $request)
    {
        $days = ProgramDay::where($request->get('week'))->get();

        return response($days, 200);
    }
}
