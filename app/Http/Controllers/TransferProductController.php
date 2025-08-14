<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Models\BranchProductModel;
use App\Models\BranchTransferModel;
use App\Models\ProductHistoryModel;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TransferProductController extends Controller
{
    
        public function index()
{
    // You now have access to the branch in the controller
    return Inertia::render('Admin/TransferProduct',[
        'branches' => AddBranchModel::select('id', 'branch_name')->get(),
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'products' => 'required|array|min:1',
        'products.*.from_branch_id' => 'required',
        'products.*.product_id' => 'required',
        'products.*.quantity' => 'required|numeric|min:1',
        'products.*.to_branch_id' => 'required',
        'products.*.cost_price' => 'required',
        'products.*.retail_price' => 'required',
        'products.*.wholesale_price' => 'required',
    ]);

    foreach ($validated['products'] as $item) {
        $fromBranchId = $item['from_branch_id'];
        $toBranchId = $item['to_branch_id'];
        $productId = $item['product_id'];
        $quantity = $item['quantity'];

        // Fetch product from source branch
        $fromProduct = BranchProductModel::where('branch_id', $fromBranchId)
            ->where('product_id', $productId)
            ->first();

        if (!$fromProduct || $fromProduct->quantity < $quantity) {
            return response()->json([
                'error' => "Insufficient stock in branch ID $fromBranchId for product ID $productId.",
            ], 400);
        }

        // Deduct quantity from source branch
        $fromProduct->quantity -= $quantity;
        $fromProduct->save();

        // Fetch or create product for destination branch
        $toProduct = BranchProductModel::where('branch_id', $toBranchId)
            ->where('product_id', $productId)
            ->first();

        if ($toProduct) {
            // ✅ Update existing: Add quantity and overwrite prices from request
            $toProduct->quantity += $quantity;
            $toProduct->cost_price = $item['cost_price'];
            $toProduct->retail_price = $item['retail_price'];
            $toProduct->wholesale_price = $item['wholesale_price'];
            $toProduct->save();
        } else {
            // ✅ Create new with prices and quantity from request
            BranchProductModel::create([
                'branch_id' => $toBranchId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'cost_price' => $item['cost_price'],
                'retail_price' => $item['retail_price'],
                'wholesale_price' => $item['wholesale_price'],
            ]);
        }

        // Log product history
        $this->logProductHistory($productId, $toBranchId, '4', $quantity);

        // Log transfer
        BranchTransferModel::create([
            'from_branch_id' => $fromBranchId,
            'to_branch_id' => $toBranchId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'transferred_by' => Auth::id(),
        ]);
    }

    return redirect()->back()->with('success', 'Transfer completed.');
}



private function logProductHistory($productId, $branchId, $status, $quantity)
{
    $product = ProductModel::find($productId);
    $branchProduct = BranchProductModel::where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->first();

    if (!$product || !$branchProduct) return;

    ProductHistoryModel::create([
        'account' => Auth::user()->acct_name,
        'branch_id' => $branchId,
        'supplier_id' => $product->supplier_id,
        'category_id' => $product->category_id,
        'product_code' => $product->product_code,
        'description' => $product->description,
        'cost_price' => $branchProduct->cost_price,
        'retail_price' => $branchProduct->retail_price,
        'wholesale_price' => $branchProduct->wholesale_price,
        'quantity' => $quantity,
        'status' => $status,
    ]);
}


}
