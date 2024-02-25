<?php

namespace App\Http\Controllers;

use App\Models\Hobby;
use App\Models\Translation;
use const App\Constants\EnumList\Enums;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GeneralController extends Controller
{
    public function translations(): Response
    {
        $translations = Translation::where('language', 'he')
            ->select('name', 'value')
            ->get()
            ->mapWithKeys(function($row) {
                return [$row->name => $row->value];
            });

        return response($translations, 200);
    }

    public function options(Request $request): Response
    {
        $enum = config('constants.enums.' . $request->name);
        if (!$enum) {
            return response(['message' => 'No options found'], 422);
        }

        return response($enum, 200);
    }

    public function hobbies()
    {
        $hobbies = Hobby::select('id', 'name')->get();
        return response($hobbies, 200);
    }

    public function sentences()
    {
        $sentences = Translation::select('id', 'name')
            ->where('language', 'he')
            ->where('related_to', 'sentences')
            ->get();

        return response($sentences, 200);
    }
}
