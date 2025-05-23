<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Mail\ResetPassword;
use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
            return response(['message' => 'Wrong credentials'], 401);
        }

        if (!$user->is_active) {
            return response(['message' => 'User is inactive'], 403);
        }

        $token = $user->createToken('login')->plainTextToken;

        $isFilledDetails=$user->city!=null&&$user->city!='';
        $response = [
            ...$user->jsonSerialize(),
            'token'                 => $token,
            'is_paid'               => $user->sales()->exists(),
            'is_filled_details' => $isFilledDetails,
            'is_done_registration'  => $user->subCategories && $user->subCategories->count() > 0
        ];

        return response($response, 200);
    }

    public function store(UserRequest $request)
    {
        $request->validate([
            'first_name'    => 'required|max:30',
            'last_name'     => 'required|max:30',
            'password'      => 'required|max:30',
            'mobile'        => 'required|max:30',
            'email'         => 'required|max:150|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/',
        ]);

        // $emailOrMobileExists = User::where('email', $request->email)
        //     ->orWhere('mobile', $request->mobile)->exists();
        $emailOrMobileExists = User::where('email', $request->email)
        ->orWhere(function($query) use ($request) {
            $query->where('mobile', $request->mobile)
                  ->whereNotNull('mobile');  // Ensure mobile is not null
        })
        ->exists();

        if ($emailOrMobileExists) {
            return response(['message' => 'Email or mobile already exists'], 409);
        }

        $user = User::saveInstance($request->all());

        $token = $user->createToken('login')->plainTextToken;

        $response = [
            ...$user->jsonSerialize(),
            'token'                 => $token,
            // 'is_paid'               => !!$user->payment_package_id,
            'is_paid'               => $user->sales()->exists(),
            'is_filled_details' => false,
            'is_done_registration'  => false,
        ];

        return response($response, 200);
    }

    public function update(UserRequest $request)
    {
        $user = User::where('email', Auth::user()->email)->first();

        //validate unique mobile
        $mobileExists = User::where('mobile', $request->mobile)
    ->where('id', '!=', $user->id)  // Exclude the current user
    ->exists();
        
        if ($mobileExists) {
            return response(['message' => 'Mobile already exists'], 409);
        }


        $password = $request->get('password') ?? $user->password;
        $email = $user->email;

        $user->fill($request->all());

        $user->password = $password;
        $user->email = $email;
        $user->save();

        $token = $user->createToken('login')->plainTextToken;

        $isFilledDetails=$user->city!=null&&$user->city!='';

        $response = [
            ...$user->jsonSerialize(),
            'token'     => $token,            
            'is_filled_details' => $isFilledDetails,
            'is_paid'               => $user->sales()->exists(),
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
            'mobile'        => 'nullable|max:20'
        ]);

        $user = User::where('social_id', $request->input('social_id'))
            ->where('provider', $request->input('provider'))
            ->orWhere('email', $request->get('email'))
            ->first();

        if (!$user) {
            if($request->input('mobile')==null || $request->input('mobile')=='')
            {
                return response(['message' => 'Mobile is required'], 400);
            }
            $user = User::saveInstance($request->all());
        }

        $token = $user->createToken('login')->plainTextToken;

        $isFilledDetails=$user->city!=null&&$user->city!='';
        $response = [
            ...$user->jsonSerialize(),
            'token'     => $token,
            'is_paid'               => $user->sales()->exists(),
            'is_filled_details' => $isFilledDetails,
            'is_done_registration'  => $user->subCategories && $user->subCategories->count() > 0
        ];

        return response($response, 200);
    }

    public function forgotPassword(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $token = PasswordResetToken::saveInstance($user->email);

        Mail::to($user->email)->send(new ResetPassword($token));

        return response(['message' => 'Mail was sent'], 200);
    }

    public function resetPassword(Request $request)
    {
        $token = PasswordResetToken::where('token', $request->post('token'))->firstOrFail();

        $duration = $token->created_at->diffInMinutes(Carbon::now());
        if ($duration > 5) {
            return response(['message' => 'Reset expired'], 400);
        }

        $password = Hash::make($request->post('password'));
        User::where('email', $token->email)->update(['password' => $password]);

        return response(['message' => 'Password was reset'], 200);

    }
}
