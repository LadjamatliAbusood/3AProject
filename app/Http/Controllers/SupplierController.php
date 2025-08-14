<?php

namespace App\Http\Controllers;

use App\Models\SupplierModel;
use App\Traits\HandleSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use function Laravel\Prompts\table;

class SupplierController extends Controller
{

    use HandleSupplier;

//    public function index(Request $request)
//     {
//         $data = $this->getSupplierData($request);
//         return inertia('Admin/Supplier', $data);
//     }
    
    public function index(Request $request){

        $Suppliers = SupplierModel::with(['product.branchProducts'])->paginate(10);

    // Compute total cost (cost_price * quantity) for each supplier
    $Suppliers->each(function ($supplier) {
        $totalCost = 0;
        foreach ($supplier->product as $product) {
            foreach ($product->branchProducts as $branch) {
                $totalCost += $branch->cost_price * $branch->quantity;
            }
        }
        $supplier->total_cost = $totalCost;
    });

        return Inertia::render('Admin/Supplier', compact('Suppliers'));

    }

    public function store(Request $request){
        $fields = $request ->validate([
            'supplier_name' => 'required|string|max:255',
            'cost' => 'required',
            'status'=> 'required|in:1,2',
        ]);
        $supplier = SupplierModel::create($fields);
          return back()->with('success', 'supplier added successfully.');
        // return redirect()->route('supplier');
    }
    public function update(Request $request, $id){
        $supplier = SupplierModel::findOrFail($id);
        $fields = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'status'=>'required|in:1,2',
        ]);
        $supplier->update($fields);
         return back()->with('success', 'supplier updated successfully.');
       // return redirect()->route('supplier', ['page'=>$request->query('page')]);
    }


 public function getSupplierSummary($supplierId)
{
    $summary = DB::table('branch_products')
        ->join('product', 'branch_products.product_id', '=', 'product.id')
        ->where('product.supplier_id', $supplierId)
        ->selectRaw('
            product.account as account,
            DATE(branch_products.created_at) as date,
            SUM(branch_products.quantity) as total_quantity,
            SUM(branch_products.cost_price * branch_products.quantity) as total_cost
        ')
        ->groupBy('account', 'date')
        ->orderByDesc('date')
        ->paginate(50);

    return response()->json($summary);
}
    

}
