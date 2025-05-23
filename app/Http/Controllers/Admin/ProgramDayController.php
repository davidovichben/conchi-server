<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramDayActivity;
use App\Models\ProgramDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramDayController extends Controller
{
    public function store(Request $request)
    {
        $day = ProgramDay::createInstance($request->post('weekId'));
        return response([...$day->toArray(), 'interactions' => []], 200);
    }

    public function update(ProgramDay $programDay, Request $request)
    {
        $labels = ['morning_label', 'afternoon_label', 'evening_label', 'night_label'];

        $property = $request->input('property');
        if (!in_array($property, $labels)) {
            return response(['message' => 'Invalid property'], 400);
        }

        $value = $request->input('value');

        $programDay->update([$property => $value]);

        return response([...$programDay->toArray(), 'interactions' => []], 200);
    }

    public function destroy(ProgramDay $programDay)
    {
        DB::beginTransaction();

        $programDay->delete();

        ProgramDay::where('week_id', $programDay->week_id)
            ->where('number', '>', $programDay->number)
            ->orderBy('number', 'asc')
            ->update(['number' => DB::raw('number - 1')]);

        DB::commit();

        return response(['message' => 'Day deleted'], 200);
    }

    public function storeActivity($dayId, Request $request)
    {
        ProgramDayActivity::upsertInstance($dayId, $request->post());

        return response(['message' => 'Activity added to day'], 200);
    }

    public function deleteActivity($dayId, Request $request)
    {
        ProgramDayActivity::where('program_day_id', $dayId)
            ->where('period', $request->get('period'))
            ->delete();

        return response(['message' => 'Activity removed from day'], 200);
    }
}
