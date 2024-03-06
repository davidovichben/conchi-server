<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\ProgramDay;
use App\Models\UserProgramDay;
use App\Models\UserProgramWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProgramDayController extends Controller
{
    public function show(ProgramDay $programDay)
    {
        $weekDays = DB::table('program_days', 'pd')
            ->leftJoin('user_program_days as upd', function($query) {
                return $query->on('upd.program_day_id', 'pd.id')->where('user_id', Auth::id());
            })
            ->where('pd.week_id', $programDay->week_id)
            ->selectRaw('pd.id, pd.number, upd.completed')
            ->get()->mapWithKeys(function($day) {
                return [$day->number => $day];
            });

//        if ($programDay->number > 1) {
//            $prevDay = $weekDays[$programDay->number - 1];
//            if (!$prevDay->completed) {
//                return response(['message' => 'Bad request'], 400);
//            }
//        }

        $query = Interaction::getQuery(Auth::id());

        $rows = $query->where('day_id', $programDay->id)->get();

        $interactions = Interaction::getInteractions($rows);

        $nextDay = $weekDays->get($programDay->number + 1);

        return response([
            'weekId'        => $programDay->week_id,
            'nextDayId'     => $nextDay ? $nextDay->id : null,
            'interactions'  => $interactions
        ], 200);
    }

    public function complete(ProgramDay $programDay)
    {
        $query = UserProgramDay::where('program_day_id', $programDay->id)->where('user_id', Auth::id());

        $userProgramDay = $query->first();
        if (!$userProgramDay || $userProgramDay->completed) {
            return response(['message' => 'Bad request'], 400);
        }

        $userProgramDay->update(['completed' => 1]);

        $weekDays = ProgramDay::where('program_week_id', $programDay->week_id)->get();
        $completedDays = UserProgramDay::where('completed', 1)->where('user_id', Auth::id())->get();

        if ($weekDays->count() === $completedDays->count()) {
             UserProgramWeek::where('user_id', Auth::id())
                ->where('program_week_id', $programDay->week_id)
                ->update(['status' => 'completed']);
        }

        return response(['message' => 'Day completed'], 200);
    }
}
