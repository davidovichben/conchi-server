<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ContentPackage;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\InteractionCategory;
use App\Models\InteractionSubCategory;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

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

    public function images(): Response
    {
        $images = Image::select('key_name', 'path')
            ->get()
            ->mapWithKeys(function($row) {
                return [$row->key_name => url(Storage::url($row->path))];
            });

        return response($images, 200);
    }

    public function hobbies()
    {
        $category = InteractionCategory::where('role', 'hobbies')->with('subCategories')->first();
        return response($category->subCategories, 200);
    }

    public function sentences()
    {
        $category = InteractionCategory::where('role', 'power_sentences')->with(['interactions' => function ($query) {
            $query->select('id', 'title', 'category_id')->orderBy('show_order', 'asc');
        }])
        ->first();

        return response($category->interactions, 200);
    }

    public function options(Request $request): Response
    {
        $options = config('constants.' . $request->name);
        if (!$options) {
            return response(['message' => 'No options found'], 422);
        }

        return response($options, 200);
    }

    public function news()
    {
        $articles = Article::all()->mapToGroups(function ($article) {
            return [$article->position => [
                ...$article->toArray(),
                'image' => $article->image ? url(Storage::url($article->image)) : null
            ]];
        });

        return response([
            'contentPackages'   => ContentPackage::all(),
            'articles'          => $articles
        ], 200);
    }
}
