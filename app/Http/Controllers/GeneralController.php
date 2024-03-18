<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ContentPackage;
use App\Models\InteractionSubCategory;
use App\Models\Translation;
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
        return response(InteractionSubCategory::all(), 200);
    }

    public function sentences()
    {
        $sentences = Translation::select('id', 'name')
            ->where('language', 'he')
            ->where('related_to', 'sentences')
            ->get();

        return response($sentences, 200);
    }

    public function news()
    {
        $articles = Article::all()->mapToGroups(function ($article) {
            return [$article->position => $article];
        });

        return response([
            'contentPackages'   => ContentPackage::all(),
            'articles'          => $articles
        ], 200);
    }
}
