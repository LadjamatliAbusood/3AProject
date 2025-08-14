<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Models\BranchProductModel;
use App\Models\BranchStockModel;
use App\Traits\HandleBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use function Laravel\Prompts\select;



class AddBranchController extends Controller
{
    use HandleBranch;


public function index(Request $request)
{
    return $this->getBranchData($request);
    // $branches = AddBranchModel::latest()->paginate(10);
    // $allBranches = AddBranchModel::orderBy('branch_name')->get();

    // $branchStats = DB::table('branch_products')
    //     ->select(
    //         'branch_id',
    //         DB::raw('SUM(quantity) as total_quantity'),
    //         DB::raw('SUM(quantity * cost_price) as total_cost')
    //     )
    //     ->groupBy('branch_id')
    //     ->get()
    //     ->keyBy('branch_id');
     

    // // Merge stats into each paginated branch
    // $branches->getCollection()->transform(function ($branch) use ($branchStats) {
    //     $stats = $branchStats[$branch->id] ?? null;
    //     $branch->total_quantity = $stats->total_quantity ?? 0;
    //     $branch->total_cost = $stats->total_cost ?? 0;
    //     return $branch;
    // });


    // return Inertia::render('Admin/Branch', [
    //     'branches' => $branches,
    //     'allBranches' => $allBranches,
    // ]);
    
}

    public function store(Request $request)
    {
        //validate
        $fields = $request->validate([
            'branch_name' => 'required|string|max:255|unique:branch,branch_name',
            'location' => 'required|string|max:255',
            'status' => 'required|in:1,2',
        ]);

    $branch = AddBranchModel::create($fields);
     return back()->with('success', 'Product Added successfully.');
    //  return redirect()->route('branch');

    }

    public function update(Request $request,$id){
        $branch = AddBranchModel::findOrFail($id);
        $fields = $request->validate([
            'branch_name' => 'required|string|max:255|unique:branch,branch_name,' . $id,
            'location' => 'required|string|max:255',
            'status' => 'required|in:1,2',
        ]);
        $branch->update($fields);
         return back()->with('success', 'Branch updated successfully.');
        // return redirect()->route('branch', ['page' => $request->query('page')]);
    }

        public function show(Request $request,$branchName)
{
    
   
    return $this->getBranch($request,$branchName);
}

public function updateQuantity(Request $request)
{
    $request->validate([
        'id' => 'required|integer|exists:branch_products,id',
        'return_quantity' => 'required|integer|min:1',
    ]);

    $branchProduct = BranchProductModel::findOrFail($request->id);
    $branchStock = $branchProduct->branchstock; // relationship must be defined

    if (!$branchStock) {
        return response()->json(['message' => 'Branch stock not found'], 404);
    }

    // Only allow returns up to the lost amount
    $currentStatus = $branchStock->quantity_status;
    if ($currentStatus >= 0) {
        return response()->json(['message' => 'No lost quantity to return.'], 400);
    }

    $returnQty = min($request->return_quantity, abs($currentStatus));

    // Update quantities
    $branchProduct->quantity += $returnQty;
    $branchProduct->save();

    $branchStock->quantity_status += $returnQty;
    $branchStock->save();

    return response()->json([
        'message' => 'Stock updated successfully.',
        'quantity' => $branchProduct->quantity,
        'quantity_status' => $branchStock->quantity_status,
    ]);
}
    // public function show($branchName){
    //     $branch = AddBranchModel::where('branch_name', $branchName)->firstOrFail();
    //     $branchStats = BranchProductModel::where('branch_id', $branch->id)
    //     ->selectRaw('SUM(quantity) as total_quantity, SUM(quantity * cost_price) as total_cost, 
    //     SUM(quantity * retail_price) as total_retail, SUM(quantity * wholesale_price) as total_wholesale')
    //     ->first();
    //       $products = BranchProductModel::with('product') // eager load product info
    //     ->where('branch_id', $branch->id)
    //     ->get();

    //       return Inertia::render('Admin/BranchDetails', [
    //     'branch' => $branch,
    //     'total_quantity' => $branchStats->total_quantity ?? 0,
    //     'total_cost' => $branchStats->total_cost ?? 0,
    //     'total_retail' => $branchStats->total_retail ?? 0,
    //     'total_wholesale' => $branchStats->total_wholesale ?? 0,
    //     'products' => $products,
    // ]);
    // }
}
