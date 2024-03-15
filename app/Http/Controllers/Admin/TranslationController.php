<?php

namespace App\Http\Controllers\Admin;


use App\Models\Translation;
use App\Services\DataTableManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranslationController extends BaseController
{
    public function index(Request $request)
    {
        $query = Translation::query();

        $paginator = DataTableManager::getInstance($query, $request->all(), ['id', 'name', 'value'])->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function update(Translation $translation, Request $request)
    {
        $translation->update(['value' => $request->get('value')]);

        return response(['message' => 'Translated updated'], 200);
    }
}
