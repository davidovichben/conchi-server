<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'first_name'    => 'required|max:30',
            'last_name'     => 'required|max:30',
//            'email'         => 'required|max:150|regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/',
            'mobile'        => 'required|max:150|regex:/^05\\d([-]{0,1})\\d{7}$/',
            'city'          => 'required',
            'password'      => 'max:30',
        ];
    }
}
