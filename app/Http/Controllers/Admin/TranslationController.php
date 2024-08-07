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

        $columns = ['id', 'name', 'value', 'html_value'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function update(Translation $translation, Request $request)
    {
        $translation->update([
            'value'         => $request->get('value'),
            'html_value'    => $request->get('htmlValue')
        ]);

        return response(['message' => 'Translation updated'], 200);
    }
}
