<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramReportQuestion;
use Illuminate\Http\Request;

class ProgramReportQuestionController extends Controller
{
    public function store(Request $request)
    {
        $question = ProgramReportQuestion::createInstance($request->post());

        return response($question, 201);
    }

    public function update(Request $request, ProgramReportQuestion $reportQuestion)
    {
        $reportQuestion->update(['content' => $request->post('content')]);

        return response(['message' => 'Question updated'], 200);
    }

    public function destroy(ProgramReportQuestion $reportQuestion)
    {
        $reportQuestion->delete();

        return response(['message' => 'Question deleted'], 200);
    }
}
