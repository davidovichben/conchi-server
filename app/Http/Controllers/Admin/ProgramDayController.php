<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InteractionDay;
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

    public function storeInteraction($dayId, Request $request)
    {
        InteractionDay::createInstance($dayId, $request->post());

        return response(['message' => 'interaction added to day'], 200);
    }

    public function deleteInteraction($dayId, Request $request)
    {
        InteractionDay::where('day_id', $dayId)
            ->where('interaction_id', $request->get('interactionId'))
            ->where('period', $request->get('period'))
            ->delete();

        return response(['message' => 'interaction removed from day'], 200);
    }
}
