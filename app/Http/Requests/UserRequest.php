<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'first_name'    => 'max:30',
            'last_name'     => 'max:30',
            'mobile'        => 'max:150|regex:/^05\\d([-]{0,1})\\d{7}$/',
            'city'          => 'max:100',
            'password'      => 'max:30',
            'street'        => 'nullable|max:100', // Street name can be up to 100 characters
            'number'        => 'nullable|numeric|max:9999', // Numeric value for house/building number
            'apartment'     => 'nullable|numeric|max:999', // Optional numeric value for apartment number
            'floor'         => 'nullable|numeric|max:100', // Optional numeric value for floor
            'zip_code'      => 'nullable|regex:/^[0-9]{5,7}$/', // Postal code: 5-7 numeric characters
            'address_comment'  => 'nullable|max:255',

        ];
    }
}
