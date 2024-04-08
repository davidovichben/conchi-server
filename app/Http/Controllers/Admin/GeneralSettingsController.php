<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $settings = GeneralSetting::all()->mapWithKeys(function ($setting) {
            return [$setting->name => $setting->value];
        });

        return response($settings, 200);
    }

    public function update(Request $request)
    {
        collect($request->post())->each(function($value, $name) {
            var_dump($name);

            GeneralSetting::where('name', $name)->update(['value' => $value]);
        });

        return response(['message' => 'Settings updated'], 200);
    }
}
