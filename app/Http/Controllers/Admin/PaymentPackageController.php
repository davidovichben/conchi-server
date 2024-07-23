<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentPackage;
use App\Services\DataTableManager;
use Illuminate\Http\Request;

class PaymentPackageController extends BaseController
{
    public function index(Request $request)
    {
        $query = PaymentPackage::withCount('users');

        $columns = ['title', 'price'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        $paymentPackage = PaymentPackage::createInstance($request->post());

        return response($paymentPackage, 201);
    }

    public function update(Request $request, PaymentPackage $paymentPackage)
    {
        $paymentPackage->updateInstance($request->post());

        return response($paymentPackage, 200);
    }

    public function destroy(PaymentPackage $paymentPackage)
    {
        $paymentPackage->loadCount('users');
        if ($paymentPackage->users_count > 0) {
            return response(['message' => 'Payment package related to users'], 400);
        }

        $paymentPackage->delete();

        return response(['message' => 'Payment package deleted'], 200);
    }
}
