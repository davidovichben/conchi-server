<?php

namespace App\Http\Controllers;

use App\Models\Interaction;

use App\Models\UserInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InteractionController extends Controller
{
    public function index($dayId)
    {
        $interactions = DB::table('interactions', 'i')
            ->where('day_id', $dayId)
            ->join('interaction_categories as ic', 'ic.id', 'i.id')
            ->leftJoin('user_interactions as ui', function($query) {
               return $query->on('interaction_id', 'i.id')->where('user_id', Auth::id());
            })
            ->selectRaw('i.id, ui.liked, ui.status, ic.name as category, guidelines, period, duration, title, description')
            ->get();

        $mapped = $interactions->map(function($interaction) {
            $file = 'users/3/name.webm';

            return [
                ...(array)$interaction,
                'guidelines'    => json_decode($interaction->guidelines),
                'audio'         => [
                    'file'      => 'data:audio/webm;codecs=opus;base64,' . base64_encode(Storage::get($file)),
                    'duration'  => 5
                ]
            ];
        });

        return response($mapped, 200);
    }

    public function like($interactionId, Request $request)
    {
        $liked = $request->get('liked') ? 1 : 0;

        UserInteraction::where('interaction_id', $interactionId)
            ->where('user_id', Auth::id())
            ->update(['liked' => $liked]);

        return response(['message' => 'Interaction liked'], 200);
    }

    public function setStatus($interactionId)
    {
        $userInteraction = UserInteraction::where('interaction_id', $interactionId)
            ->where('user_id', Auth::id())
            ->first();

        if ($userInteraction->status === 'completed') {
            return response(['message' => 'Status already set'], 400);
        }

        $status = $userInteraction->status === 'initial' ? 'started' : 'completed';
        $userInteraction->update(['status' => $status]);

        return response(['message' => 'Interaction status updated'], 200);
    }
}
