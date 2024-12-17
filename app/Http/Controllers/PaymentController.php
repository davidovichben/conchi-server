<?php

namespace App\Http\Controllers;
use App\Models\Coupon;
use App\Models\PaymentPackage;
use App\Models\ProgramWeek;
use App\Models\Sale;
use App\Models\User;
use App\Models\UserProgramWeek;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class PaymentController extends Controller
{
    public function index()
    {
        $packages = PaymentPackage::all();
        return response($packages, 200);
    }

    public function url(PaymentPackage $paymentPackage)
    {

        $products = [
            [
                'Description' => $paymentPackage->title,
                'UnitCost' => $paymentPackage->price
            ]
        ];
        $finalPrice =$paymentPackage->price;
        $couponCode = request()->query('couponCode');
        $couponId=null;
        if ($couponCode) {
            //get coupon by id  
            $coupon = Coupon::where('code', $couponCode)->first();
            if (!$coupon) {
                return response(['error' => 'coupon_not_found'], 404);

            }

            if(!$coupon->isValid())
            {
                return response(['error' => 'coupon_not_valid'], 400);
            }
            $discountAmount = 0;

            $discountAmount = ($paymentPackage->price * $coupon->discount) / 100;
            $finalPrice = $paymentPackage->price - $discountAmount;
            $products[] = [
                'Description' => 'קופון: ' . $coupon->discount.'% הנחה',  
                'UnitCost' => -$discountAmount 
            ];
            $couponId = $coupon->id;   
            //test webhook
            // $mockData = [
            //     'ResponseCode' => 0,  // Simulating a successful response
            //     'ReturnValue' => json_encode([
            //         'userId' => Auth::id(),
            //         'paymentPackageId' => $paymentPackage->id,
            //         'couponId' => $couponId,  // Optional, can be omitted for testing
            //     ])
            // ];
            // $mockRequest = new Request($mockData);
            // $this->webhook($mockRequest);
        }
        $request = [
            'json' => [
                'TerminalNumber' => config('services.cardcom.terminal'),
                'ApiName' => config('services.cardcom.username'),
                'ReturnValue' => json_encode([
                            'paymentPackageId' => $paymentPackage->id,
                            'userId' => Auth::id(),
                            'couponId' => $couponId
                        ]),
                'Amount' => $finalPrice,
                'SuccessRedirectUrl' => config('app.client_url') . '/payment/success',
                'FailedRedirectUrl' => config('app.client_url') . '/payment/error',
                'WebHookUrl' => url('/api/payment/webhook'),
                'Document' => [
                        'Name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                        'Email' => Auth::user()->email,
                        'Products' => $products,
                    ]
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        $client = new Client();

        $response = $client->post(config('services.cardcom.url') . '/LowProfile/Create', $request);

        $body = $response->getBody()->getContents();

        return response(['url' => json_decode($body)->Url],  200);
    }

    public function webhook(Request $request)
    {
        $responseCode = $request->input('ResponseCode');
        if ($responseCode !== 0) {
            return;
        }

        $returnValue = json_decode($request->input('ReturnValue'));

        $sale = new Sale();
        $sale->user_id = $returnValue->userId;
        $sale->payment_package_id = $returnValue->paymentPackageId;
        $sale->coupon_id = $returnValue->couponId ?? null;  // Default to null if no couponId
        $sale->date = Carbon::now()->toDateString();  // Use the current date
        $sale->save();

        //now the payment_package_id is saved in sales table
        //User::where('id', $returnValue->userId)->update(['payment_package_id' => $returnValue->paymentPackageId]);

        $programWeek = ProgramWeek::orderBy('number')->first();

        $values = [
            'status' => 'active',
            'program_week_id' => $programWeek->id,
            'user_id' => $returnValue->userId
        ];

        UserProgramWeek::saveInstance($values);
    }
}
