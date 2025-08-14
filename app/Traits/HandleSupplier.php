<?php

namespace App\Traits;

use App\Models\AddBranchModel;
use App\Models\SupplierModel;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

trait HandleSupplier{

    // public function getSupplierData(Request $request)
    // {
          
          
        
    //     $Suppliers = SupplierModel::with(['product.branchProducts'])->paginate(10);

    // $Suppliers->each(function ($supplier) {
    //     $totalCost = 0;
    //     foreach ($supplier->product as $product) {
    //         foreach ($product->branchProducts as $branch) {
    //             $totalCost += $branch->cost_price * $branch->quantity;
    //         }
    //     }
    //     $supplier->total_cost = $totalCost;
    // });

    //      return [
        
    //     'Suppliers' => $Suppliers,
        
        
    // ];
       
      

    //     }
}