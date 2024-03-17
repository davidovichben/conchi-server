<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramReportOption;
use Illuminate\Http\Request;

class ProgramReportOptionController extends Controller
{
    public function store(Request $request)
    {
        $option = ProgramReportOption::createInstance($request->post());

        return response($option, 201);
    }

    public function update(Request $request, ProgramReportOption $reportOption)
    {
        $reportOption->update(['content' => $request->post('content')]);

        return response(['message' => 'Option updated'], 200);
    }

    public function destroy(ProgramReportOption $reportOption)
    {
        $reportOption->delete();

        return response(['message' => 'Option deleted'], 200);
    }
}
