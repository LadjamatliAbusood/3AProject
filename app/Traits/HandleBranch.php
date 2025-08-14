<?php

namespace App\Traits;

use App\Models\AddBranchModel;
use App\Models\BranchProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

Trait HandleBranch{
    
    
    
  public function getBranch(Request $request, $branchName)
{
    $branch = AddBranchModel::where('branch_name', $branchName)->firstOrFail();
    $search = $request->input('search');

    $productsQuery = BranchProductModel::with([
        'product.category',
        'branchstock' => function ($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        }
    ])
    ->where('branch_id', $branch->id)
    ->when($search, function ($query, $search) {
        $query->whereHas('product', function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('product_code', 'like', "%{$search}%")
              ->orWhereHas('category', function ($catQuery) use ($search) {
                  $catQuery->where('category_name', 'like', "%{$search}%");
              });
        });
    });

    $products = $productsQuery->paginate(150)->withQueryString();

    $filteredStatsQuery = BranchProductModel::where('branch_id', $branch->id)
        ->when($search, function ($query, $search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        })
        ->selectRaw('
            SUM(quantity) as total_quantity,
            SUM(quantity * cost_price) as total_cost,
            SUM(quantity * retail_price) as total_retail,
            SUM(quantity * wholesale_price) as total_wholesale
        ')
        ->first();

    return Inertia::render('Admin/BranchDetails', [
        'branch' => $branch,
        'total_quantity' => $filteredStatsQuery->total_quantity ?? 0,
        'total_cost' => $filteredStatsQuery->total_cost ?? 0,
        'total_retail' => $filteredStatsQuery->total_retail ?? 0,
        'total_wholesale' => $filteredStatsQuery->total_wholesale ?? 0,
        'products' => $products,
        'searchTerm' => $search,
    ]);
}





    public function getBranchData(Request $request){
      

    $branches = AddBranchModel::latest()->paginate(50);

    
    $allBranches = AddBranchModel::orderBy('branch_name')->get();

    $branchStats = DB::table('branch_products')
        ->select(
            'branch_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(quantity * cost_price) as total_cost')
        )
        ->groupBy('branch_id')
        ->get()
        ->keyBy('branch_id');
     

    // Merge stats into each paginated branch
    $branches->getCollection()->transform(function ($branch) use ($branchStats) {
        $stats = $branchStats[$branch->id] ?? null;
        $branch->total_quantity = $stats->total_quantity ?? 0;
        $branch->total_cost = $stats->total_cost ?? 0;
        return $branch;
    });


    return Inertia::render('Admin/Branch', [
        'branches' => $branches,
        'allBranches' => $allBranches,
    ]);

    }
}