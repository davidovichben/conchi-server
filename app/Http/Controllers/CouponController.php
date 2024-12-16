<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;
use Exception;

class CouponController extends Controller
{
    /**
     * Check if a coupon is valid.
     */
    public function isCouponValid(Request $request)
    {
        $code = $request->input('code');
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found.'], 404);
        }

        if (!$coupon->isValid()) {
            return response()->json(['message' => 'Coupon is not valid.'], 400);
        }

        return response()->json(['message' => 'Coupon is valid.'], 200);
    }

    /**
     * Apply a coupon to an order amount.
     */
    public function useCoupon(Request $request)
    {
        $code = $request->input('code');
        $amount = $request->input('amount');

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found.'], 404);
        }

        try {
            $discountedTotal = $coupon->apply($amount);
            $coupon->save(); // Save changes to timesUsed
            return response()->json(['discountedTotal' => $discountedTotal], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
