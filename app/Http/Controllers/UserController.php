<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'     => 'required|max:150|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/',
            'password'  => 'required|max:30'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response(['messages' => 'Wrong credentials'], 401);
        }

        if (!$user->is_active) {
            return response(['message' => 'User is inactive'], 403);
        }

        $token = $user->createToken('login')->plainTextToken;

        $response = [
            ...$user->jsonSerialize(),
            'token'                 => $token,
            'is_paid'               => !!$user->payment_package_id,
            'is_done_registration'  => $user->sentences && $user->sentences->count() > 0
        ];

        return response($response, 200);
    }

    public function store(UserRequest $request)
    {
        $request->validate([
            'password'      => 'required|max:30',
            'email'         => 'required|max:150|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/',
        ]);


        $emailOrMobileExists = User::where('email', $request->email)
            ->orWhere('mobile', $request->mobile)->exists();

        if ($emailOrMobileExists) {
            return response(['message' => 'Email or mobile already exists'], 409);
        }

        $user = User::saveInstance($request->all());

        $token = $user->createToken('login')->plainTextToken;

        $response = [
            ...$user->jsonSerialize(),
            'token'                 => $token,
            'is_paid'               => !!$user->payment_package_id,
            'is_done_registration'  => false
        ];

        return response($response, 200);
    }

    public function show()
    {

    }

    public function update(UserRequest $request)
    {
        $user = User::where('email', Auth::user()->email)->first();

        $password = $request->get('password') ?? $user->password;
        $email = $user->email;

        $user->fill($request->all());

        $user->password = $password;
        $user->email = $email;
        $user->save();

        $token = $user->createToken('login')->plainTextToken;

        $response = [
            ...$user->jsonSerialize(),
            'token'     => $token,
            'is_paid'   => !!$user->payment_package_id
        ];

        return response($response, 200);
    }

    public function socialLogin(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|max:30',
            'last_name'     => 'required|max:30',
            'email'         => 'required|max:150|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/',
            'social_id'     => 'nullable|string',
            'provider'      => 'nullable|in:GOOGLE,FACEBOOK',
        ]);

        $user = User::where('social_id', $request->input('social_id'))
            ->where('provider', $request->input('provider'))
            ->orWhere('email', $request->get('email'))
            ->first();

        if (!$user) {
            $user = User::saveInstance($request->all());
        }

        $token = $user->createToken('login')->plainTextToken;

        $response = [
            ...$user->jsonSerialize(),
            'token'     => $token,
            'is_paid'   => !!$user->payment_package_id
        ];

        return response($response, 200);
    }
}
