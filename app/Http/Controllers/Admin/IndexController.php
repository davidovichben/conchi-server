<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class IndexController extends BaseController
{
    public function login(Request $request)
    {
        if ($request->username !== config('auth.admin_username')) {
            return response(['message' => 'Bad request'], 400);
        }

        $user = User::where('email', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response(['message' => 'Bad request'], 400);
        }

        $token = $user->createToken('admin')->plainTextToken;

        return response(['token' => $token], 200);
    }
}
