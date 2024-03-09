<?php

namespace App\Http\Controllers\Admin;

class BaseController
{
    protected function dataTableResponse($paginator)
    {
        return response([
            'items'     => $paginator->items(),
            'total'     => $paginator->total(),
            'lastPage'  => $paginator->lastPage()
        ], 200);
    }
}
