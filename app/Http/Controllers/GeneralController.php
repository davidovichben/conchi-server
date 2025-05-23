<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\City;
use App\Models\ContentPackage;
use App\Models\Media;
use App\Models\Page;
use App\Models\Rating;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Cache;
class GeneralController extends Controller
{
    public function translations(): Response
    {
        // Cache key
        $cacheKey = 'translations_he';

        // Retrieve or refresh cache
        $translations = Cache::remember($cacheKey, now()->addHours(1), function () {
            return Translation::where('language', 'he')
                ->select('name', 'value', 'html_value')
                ->get()
                ->mapWithKeys(function ($row) {
                    return [$row->name => $row->value ?? $row->html_value];
                });
        });

        return response($translations, 200);
    }

    public function media(): Response
    {       
        $cacheKey = 'media_data';
        
        $media = Cache::remember($cacheKey, now()->addHours(1), function () {
            return Media::select('key_name', 'path')
                ->get()
                ->mapWithKeys(function ($row) {
                    return [$row->key_name => url(Storage::url($row->path))];
                });
        });

        return response($media, 200);
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

    public function article(Article $article)
    {
        return response($article, 200);
    }

    public function page(Request $request)
    {
        $page = Page::where('type', $request->get('type'))->select('title', 'content')->first();
        return response($page, 200);
    }

    public function cities()
    {
        $cities = City::orderBy('name')->get();
        return response($cities, 200);
    }

    public function ratings(Request $request)
    {
        $baseQuery = Rating::where('type', $request->get('type'));

        $query = $baseQuery->select('score', 'content', 'path', 'author');

        $ratings = $query->get()->map(function($row) {
            return [
                'score'     => $row->score,
                'content'   => $row->content,
                'author'    => $row->author,
                'image'     => $row->path ? url(Storage::url($row->path)) : null
            ];
        });

        return response($ratings, 200);
    }
}
