<?php

namespace App\Http\Controllers;

use App\Models\UserDetail;
use App\Models\UserHobby;
use App\Models\UserSentence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDetailsController extends Controller
{
    public function show()
    {
        $details = UserDetail::where('user_id', Auth::id())->get();
        $hobbies = UserHobby::where('user_id', Auth::id())->get();
        $sentences = UserSentence::where('user_id', Auth::id())->get();

//        $a = compact($details, $hobbies, $sentences);
//        var_dump($a);

        return response([
            'details'   => $details,
            'hobbies'   => $hobbies,
            'sentences' => $sentences
        ], 200);
    }

    public function update(Request $request)
    {

    }
}
