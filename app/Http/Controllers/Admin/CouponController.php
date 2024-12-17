<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Services\DataTableManager;


class CouponController extends BaseController
{
    /**
     * List all coupons.
     */
    public function index(Request $request)
    {
        // Query coupons with any necessary relationships or counts
        $query = Coupon::query();

        $columns = ['id','code', 'discount', 'start_date','end_date', 'is_active'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'discount' => 'required|numeric|min:0|max:100', // Percent discount between 0 and 100
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        // Create the coupon
        $coupon = Coupon::create($validated);

        return response($coupon, 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        // Validate the request data
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'discount' => 'required|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        // Update the coupon
        $coupon->update($validated);

        return response($coupon, 200);
    }

    public function destroy(Coupon $coupon)
    {
        // Check any additional constraints (e.g., if related to another model)
        if ($coupon->times_used > 0) {
            return response(['message' => 'Coupon has been used and cannot be deleted'], 400);
        }

        $coupon->delete();

        return response(['message' => 'Coupon deleted'], 200);
    }
}
