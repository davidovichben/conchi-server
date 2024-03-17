<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    public function index(Request $request)
    {
        $query = Article::query();

        $columns = ['title', 'description', 'position'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        $article = Article::createInstance($request->post());

        return response($article, 201);
    }

    public function update(Request $request, Article $article)
    {
        $article->updateInstance($request->post());

        return response($article, 200);
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return response(['message' => 'Article deleted'], 200);
    }
}
