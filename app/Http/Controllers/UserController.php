<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Nette\Schema\ValidationException;

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

        $token = $user->createToken('login')->plainTextToken;

        $response = [
            ...$user->jsonSerialize(),
            'token'     => $token,
            'is_paid'   => !!$user->payment_package_id
        ];

        return response($response, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|max:30',
            'last_name'     => 'required|max:30',
            'email'         => 'required|max:150|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/',
            'mobile'        => 'required|max:150|regex:/^05\\d([-]{0,1})\\d{7}$/',
            'city'          => 'required',
            'password'      => 'required|max:30',
        ]);

        $user = User::saveInstance($request->all());

        $token = $user->createToken('login')->plainTextToken;

        $response = [
            ...$user->jsonSerialize(),
            'token'     => $token,
            'is_paid'   => !!$user->payment_package_id
        ];


        return response($response, 200);
    }

    public function show()
    {

    }

    public function update(Request $request, string $id)
    {

    }
}
