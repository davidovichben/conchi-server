<?php

namespace App\Http\Controllers;

use App\Models\PaymentPackage;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        $packages = PaymentPackage::all();
        return response($packages, 200);
    }

    public function url(PaymentPackage $paymentPackage)
    {
        $request = [
            'json' => [
                'TerminalNumber'        => config('services.cardcom.terminal'),
                'ApiName'               => config('services.cardcom.username'),
                'ReturnValue'           => Auth::id(),
                'Amount'                => $paymentPackage->price,
                'SuccessRedirectUrl'    => config('app.client_url') . '/payment/success',
                'FailedRedirectUrl'     => config('app.client_url') . '/payment/error',
                'WebHookUrl'            => url('/api/payment/webhook'),
                'Document'              => [
                    'To'        => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                    'Email'     => Auth::user()->email,
                    'Products' => [
                        $paymentPackage->title
                    ],
                ]
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        $client = new Client();

        $response = $client->post(config('services.cardcom.url') . '/LowProfile/Create', $request);

        $body = $response->getBody()->getContents();

//        var_dump($body);

        return response(['url' => 'http://localhost:4200'], 200);
    }

    public function webhook(Request $request)
    {

    }
}
