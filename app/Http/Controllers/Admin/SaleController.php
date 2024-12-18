<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Services\DataTableManager;
use DB;
class SaleController extends BaseController
{  
    public function index(Request $request)
    {
        // Start the query with a join for related data
        $query = DB::table('sales')
            ->select([
                'sales.id',
                'sales.user_id',  // No alias, keep the original field name
                'users.email',
                'sales.amount',
                'sales.payment_package_id',
                'pp.title as package_title',
                'coupons.code as coupon_code',
                'sales.coupon_id',
                'sales.date',
            ])
            ->leftJoin('users', 'sales.user_id', '=', 'users.id') // Join users table
            ->leftJoin('payment_packages as pp', 'sales.payment_package_id', '=', 'pp.id') // Join payment_packages table
            ->leftJoin('coupons', 'sales.coupon_id', '=', 'coupons.id'); // Join coupons table
    
        // Define the columns available for filtering/sorting
        $columns = [
            'users.email',
            'pp.title',
            'coupons.code',
            'sales.date',
        ];
    
        // Use the DataTableManager for pagination and filtering
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();
    
        return $this->dataTableResponse($paginator);
    }

    /**
     * Store a new sale.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_package_id' => 'required|exists:payment_packages,id',
            'coupon_id' => 'nullable|exists:coupons,id',
            'date' => 'required|date',
        ]);

        // Create the sale
        $sale = Sale::create($validated);

        return response($sale, 201);
    }

    /**
     * Update an existing sale.
     */
    public function update(Request $request, Sale $sale)
    {
        // Validate the request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_package_id' => 'required|exists:payment_packages,id',
            'coupon_id' => 'nullable|exists:coupons,id',
            'date' => 'required|date',
        ]);

        // Update the sale
        $sale->update($validated);

        return response($sale, 200);
    }

    /**
     * Delete a sale.
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();

        return response(['message' => 'Sale deleted'], 200);
    }
}
