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
        $programDay->load('interactions');
        $programDay->interactions->load('audioFiles')
            ->load('category')
            ->load(['userInteractions' => function($query) {
                $query->where('user_id', Auth::id());
            }]);

        $user = Auth::user()->load('details');

        $interactions = $programDay->interactions->mapWithKeys(function ($interaction) {
            return [$interaction->pivot->period => $interaction];
        });

        $interactions = $interactions->map(function($interaction) use ($user) {
            $values = [
                ...$interaction->getAttributes(),
                'guidelines'    => $interaction->guidelines,
                'liked'         => $interaction->userInteractions->count() > 0,
                'status'        => $interaction->userInteractions->count() > 0 ? $interaction->userInteractions->first()->status : null,
                'category'      => $interaction->category ? [
                    'id'    => $interaction->category->id,
                    'name'  => $interaction->category->name,
                    'image' => url(Storage::url($interaction->category->image))
                ] : null,
            ];

            if ($interaction->userInteractions->count() > 0) {
                $values['status'] = $interaction->userInteractions->first()->status;
                $values['liked'] = $interaction->userInteractions->first()->liked;
            }

            $audioFile = $interaction->selectAudioFile($user->details);
            if ($audioFile) {
                $values['audio'] = url(Storage::url($audioFile->file));
                $values['duration'] = $audioFile->duration;
            }

            return $values;
        });

        $nextDay = $programDay->nextDay();

        return response([
            'weekId'        => $programDay->week_id,
            'nextDayId'     => $nextDay ? $nextDay->id : null,
            'interactions'  => $interactions
        ], 200);
    }

    public function complete(ProgramDay $programDay)
    {
        $userProgramDay = UserProgramDay::where('interaction_id', $programDay->id)
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

        UserProgramDay::createInstance($values);

        $weekDaysCount = ProgramDay::where('program_week_id', $programDay->week_id)->count();
        $completedDaysCount = UserProgramDay::where('completed', 1)->where('user_id', Auth::id())->count();

        if ($weekDaysCount === $completedDaysCount) {
             UserProgramWeek::where('user_id', Auth::id())
                ->where('program_week_id', $programDay->week_id)
                ->update(['status' => 'completed']);
        }

        return response(['message' => 'Day completed'], 200);
    }
}
