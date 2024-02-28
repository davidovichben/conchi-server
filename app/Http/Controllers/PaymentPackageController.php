<?php

namespace App\Http\Controllers;

use App\Models\PaymentPackage;
use Illuminate\Http\Request;

class PaymentPackageController extends Controller
{
    public function index()
    {
        $packages = PaymentPackage::all();
        return response($packages, 200);
    }
}
