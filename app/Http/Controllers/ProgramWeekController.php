<?php

namespace App\Http\Controllers;

use App\Models\ProgramDay;
use App\Models\ProgramReportQuestion;
use App\Models\ProgramWeek;
use App\Models\UserInteraction;
use App\Models\UserProgramReport;
use App\Models\UserProgramWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProgramWeekController extends Controller
{
    public function index()
    {
        $weeks = ProgramWeek::where('is_active', 1)->with(['userWeeks' => function ($query) {
            return $query->where('user_id', Auth::id());
        }])->get();

        $mapped = $weeks->mapWithKeys(function($week) {
            return [
                $week->id => [
                    'id'            => $week['id'],
                    'description'   => $week['description'],
                    'number'        => $week['number'],
                    'status'        => count($week['userWeeks']) > 0 ? $week['userWeeks'][0]['status'] : 'locked',
                    'image'         => $week->image ? url(Storage::url($week->image)) : null
                ]
            ];
        });

        return response($mapped->values(), 200);
    }

    public function show(ProgramWeek $programWeek) {
        return response([
            'id'          => $programWeek->id,
            'description' => $programWeek->description,
            'number'      => $programWeek->number,
            'image'       => $programWeek->image ? url(Storage::url($programWeek->image)) : null
        ], 200);
    }

    public function days($weekId)
    {
        $days = ProgramDay::where('week_id', $weekId)
            ->leftJoin('user_program_days as upd', function ($query) {
                $query->on('program_days.id', 'upd.program_day_id')->where('user_id', Auth::id());
            })
            ->select('program_days.id', 'upd.completed')
            ->get();


        $mapped = $days->mapWithKeys(function($day) {
            return [
                $day->id => [
                    'id'        => $day->id,
                    'completed' => (bool)$day->completed
                ]
            ];
        });

        return response($mapped->values(), 200);
    }

    public function report($weekId)
    {
        $userWeek = UserProgramWeek::where('user_id', Auth::id())->where('program_week_id', $weekId)->first();
        if (!$userWeek || $userWeek->status !== 'completed') {
            return response(['message' => 'Bad request'], 400);
        }

        $questions = ProgramReportQuestion::where('program_week_id', $weekId)
            ->with('options')
            ->with('userReport', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->get()
            ->map(function ($row) {
                return [
                    'id'             => $row->id,
                    'content'        => $row->content,
                    'options'        => $row->options,
                    'user_option'    => $row->userReport ? $row->userReport->program_report_option_id : null
                ];
            });

        return response([
            'review'    => $userWeek->review,
            'questions' => $questions
        ], 200);
    }

    public function updateReport($weekId, Request $request)
    {
        $validated = $request->validate([
            'options'   => 'array',
            'options.*' => 'nullable|integer',
            'review'    => 'max:255'
        ]);

        $userProgramWeek = UserProgramWeek::where('user_id', Auth::id())->where('program_week_id', $weekId)->first();
        if (!$userProgramWeek || $userProgramWeek->status !== 'completed') {
            return response(['message' => 'Bad request'], 400);
        }

        $questions = ProgramReportQuestion::where('program_week_id', $weekId)->select('id')->with('options')->get();
        $questionIds = $questions->map(function ($question) {
            return $question->id;
        });

        UserProgramReport::whereIn('program_report_question_id', $questionIds)->where('user_id', Auth::id())->delete();

        $options = collect($validated['options']);

        $reportInsertValues = [];
        $interactionInsertValues = []; // For interactions related to options

        foreach ($questions as $question) {
            $userOptionId = $options->get($question->id);

            if ($userOptionId) {
                $option = $question->options->first(function($option) use ($userOptionId) {
                    return $option->id === $userOptionId;
                });

                if ($option) {
                    $reportInsertValues[] = [
                        'user_id'                       => Auth::id(),
                        'program_report_question_id'    => $question->id,
                        'program_report_option_id'      => $option->id
                    ];

                    $interactionInsertValues[] = [
                        'user_id'           => Auth::id(),
                        'interaction_id'    => $option->interaction_id,
                        'selected'          => 1
                    ];
                }
            }
        }

        UserProgramReport::insert($reportInsertValues);
        UserInteraction::insert($interactionInsertValues);

        $userProgramWeek->review = $validated['review'];
        $userProgramWeek->update();

        return response(['message' => 'Report updated'], 200);
    }
}
