<?php

namespace App\Http\Controllers;

use App\Models\Interaction;

use App\Models\UserInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractionController extends Controller
{
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
