<?php

namespace App\Traits;

use App\Models\AddBranchModel;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\SupplierModel;
use Illuminate\Http\Request;

trait HandlesProductDataDatabase
{
    public function getProductData(Request $request)
{
    $user = $request->user();
    $branchId = $user->branch_id;

    $Suppliers = SupplierModel::select('id', 'supplier_name')
        ->where('status', 1)
        ->orderBy('supplier_name')
        ->get();
       
        $Branches = AddBranchModel::select('id', 'branch_name')
        ->where('status', 1)
        ->orderBy('branch_name')
        ->get();

    $Category = CategoryModel::select('id','category_name')
        ->where('status', 1)
        ->orderBy('category_name')
        ->get();


    $lastCode = ProductModel::orderBy('product_code', 'desc')->value('product_code');
    $nextNumber = $lastCode ? ((int)$lastCode + 1) : 1;
    $nextCode = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

    $Product = ProductModel::with(['supplier', 'branchProducts.branch', 'category'])
        ->when($user->acct_roles !== 1 && $user->acct_roles !== 2, function ($query) use ($branchId) {
            $query->whereHas('branchProducts', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        })
      
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%')
                  ->orWhere('product_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('supplier', function ($supplierQuery) use ($request) {
              $supplierQuery->where('supplier_name', 'like', '%' . $request->search . '%');
          })
          ->orWhereHas('branchProducts.branch', function ($BranchQuery) use ($request) {
              $BranchQuery->where('branch_name', 'like', '%' . $request->search . '%');
          })
           ->orWhereHas('category', function ($CategoryQuery) use ($request) {
              $CategoryQuery->where('category_name', 'like', '%' . $request->search . '%');
          });
          
                  
            });
        })
        
        
        ->select('id','supplier_id' ,'category_id','account', 'product_code', 'barcode', 'description')
        ->orderBy('id', 'desc')
        ->paginate(50)
        ->withQueryString();

    

    return [
        'Product' => $Product,
        'Suppliers' => $Suppliers,
        'Branches' => $Branches, 
        'nextCode' => $nextCode,
        'Category' => $Category,
        'searchTerm' => $request->search,
        
    ];
}

}
