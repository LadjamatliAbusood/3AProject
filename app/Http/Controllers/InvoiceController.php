<?php

namespace App\Http\Controllers;

use App\Models\BranchProductModel;
use App\Models\SalesReportModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Validate the incoming request
       $request->validate([
    'products' => 'required|array|min:1',
    'products.*.product_code' => 'required|string',
    'products.*.description' => 'required|string',
    'products.*.quantity' => 'required|integer|min:1',
    'products.*.selling_price' => 'required|numeric|min:0',
    'products.*.cost_price' => 'required|numeric|min:0',
    'products.*.total_price' => 'required|numeric|min:0',      
    'products.*.net_sale' => 'required|numeric|min:0',         
    'products.*.product_id' => 'required|integer|min:1',      
    'products.*.selling_type' => 'required|in:1,2',
    'pay' => 'required|numeric|min:0',
]);
       

        try {
            DB::transaction(function () use ($request) {
                $user = $request->user();
                $branchId = $user->branch_id;
                $userAcctName = $user->acct_name;

                foreach ($request->products as $item) {
                   

                    // ✅ Save to sales report
                    SalesReportModel::create([
                        'account' => $userAcctName,
                        'branch_id' => $branchId,
                        'product_id' => $item['product_id'],
                        'product_code' => $item['product_code'],
                        'description' => $item['description'],
                        'selling_price' => $item['selling_price'],
                        'selling_type' => $item['selling_type'],
                        'quantity' => $item['quantity'],
                        'total_price' => $item['total_price'],
                        'cost_price' => $item['cost_price'],
                        'net_amount' => $item['net_sale'],
                    ]);

                    // ✅ Update product quantity in branch_products table
                BranchProductModel::where('branch_id', $branchId)
    ->where('product_id', $item['product_id'])
    ->decrement('quantity', $item['quantity']);
                }
            });

            // ✅ On success
            return redirect()->back()->with('success', 'Sale recorded and stock updated.');

        } catch (\Exception $e) {
    Log::error('InvoiceController.store error: ' . $e->getMessage());
    Log::error('Stack trace: ' . $e->getTraceAsString()); // ADD THIS
    return response()->json([
        'error' => $e->getMessage(), // TEMP: Show real error in response
    ], 500);
}

    }
}
