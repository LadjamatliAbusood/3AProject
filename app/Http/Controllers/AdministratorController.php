<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Models\CategoryModel;
use App\Models\SupplierModel;
use App\Traits\HandleAccount;
use App\Traits\HandleBranch;
use App\Traits\HandleExpenses;
use App\Traits\HandleHome;
use App\Traits\HandleSalereport;
use App\Traits\HandleSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Traits\HandlesProductDataDatabase;
use App\Traits\HandleProductHistory;





class AdministratorController extends Controller
{
    use HandlesProductDataDatabase;
   use HandleProductHistory;
   use HandleAccount;
   use HandleSupplier;
   use HandleHome;
   use HandleBranch;
    use HandleSalereport;
    use HandleExpenses;
 public function index(Request $request)
{
        return $this->getHomeData($request);
}



 public function account(Request $request)
{
$data = $this->getAccountData($request);
        return inertia('Auth/Register', $data);
}

 public function history(Request $request)
    {
        $data = $this->getProductHistory($request);
        return inertia('Admin/ProductHistory', $data);
    }
 public function branch(Request $request)
    {
      return $this->getBranchData($request);
    }


  public function supplier(Request $request){

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

 public function product(Request $request)
    {
        $data = $this->getProductData($request);
        return inertia('Admin/Product', $data);
    }

      public function transfer()
    {
        
     return Inertia::render('Admin/TransferProduct', [
    
        'branches' => AddBranchModel::select('id', 'branch_name')->get()
]);

    }

 
    public function salesreport(Request $request)
{
    
 
    return $this->getSalesReport($request); 
}
    public function home(Request $request)
{
    
   
    return $this->getHomeData($request);
}

    public function show(Request $request,$branchName)
{
    
   
    return $this->getBranch($request,$branchName);
}

public function expenses(Request $request){
    return $this->getExpenses($request);
}

public function category(){
     $Categories = CategoryModel::paginate(50);
       
        return Inertia::render('Admin/Category',[
            'Categories' => $Categories,
            
        ]);

}
    
}
