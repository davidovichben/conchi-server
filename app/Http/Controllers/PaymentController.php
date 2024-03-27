<?php

namespace App\Http\Controllers;

use App\Models\PaymentPackage;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
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
                'ReturnValue'           => json_encode([
                    'paymentPackageId'    => $paymentPackage->id,
                    'userId'              => Auth::id()
                ]),
                'Amount'                => $paymentPackage->price,
                'SuccessRedirectUrl'    => config('app.client_url') . '/payment/success',
                'FailedRedirectUrl'     => config('app.client_url') . '/payment/error',
                'WebHookUrl'            => url('/api/payment/webhook'),
                'Document'              => [
                    'Name'          => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                    'Email'         => Auth::user()->email,
                    'Products'      => [
                        [
                            'Description'   => $paymentPackage->title,
                            'UnitCost'      => $paymentPackage->price
                        ]
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

        return response(['url' => json_decode($body)->Url], 200);
    }

    public function webhook(Request $request)
    {
        $responseCode = $request->input('ResponseCode');
        if ($responseCode !== 0) {
            return;
        }



        $returnValue = json_decode($request->input('ReturnValue'));

        $logger = app(Logger::class);
        $logger->info('Webhook', $returnValue);

        User::where('id', $returnValue->userId)->update(['payment_package_id' => $returnValue->paymentPackageId]);
    }
}
