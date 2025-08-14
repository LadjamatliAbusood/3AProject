<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Traits\HandlesProductDataDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashierController extends Controller
{

    
    public function index($branch)
{
    
    return Inertia::render('Cashier/CashierHome', [
        'branch' => $branch
    ]);
}


public function viewproduct(Request $request, $branch)
{
    // Get the actual branch record using the slug (or code)
    $branchData = AddBranchModel::where('branch_name', $branch)->first();

    if (!$branchData) {
        abort(404, 'Branch not found.');
    }

    $branchId = $branchData->id;
    $branchName = $branchData->branch_name;

    $search = $request->input('search');

    $ProductView = DB::table('branch_products as bp')
        ->join('product as p', 'bp.product_id', '=', 'p.id')
        ->join('category as c', 'p.category_id', '=', 'c.id')
        ->select(
            'bp.id',
            'p.product_code',
            'p.description',
            'c.category_name',
            'p.barcode',
            'bp.retail_price',
            'bp.wholesale_price',
            'bp.quantity'
        )
        ->where('bp.branch_id', $branchId)
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.description', 'like', "%$search%")
                  ->orWhere('p.product_code', 'like', "%$search%")
                  ->orWhere('c.category_name', 'like', "%$search%");
            });

          
        })
        ->orderBy('p.description')
        ->paginate(150)
        ->withQueryString();

  
    return Inertia::render('Cashier/ViewProduct', [
        'ProductView' => $ProductView,
        'branch' => $branch, // pass slug
        'branch_name' => $branchName,
        'searchTerm' => $request->search,
    ]);
}


public function receipt(Request $request){

    return Inertia::render('Invoice/Receipt');
}



}
