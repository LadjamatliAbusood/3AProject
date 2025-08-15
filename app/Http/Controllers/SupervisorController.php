<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Models\BranchProductModel;
use App\Models\BranchStockModel;
use App\Models\BranchTransferModel;
use App\Models\ExpensesModel;
use App\Models\ProductModel;
use App\Traits\HandleAccount;
use App\Traits\HandleBranch;
use App\Traits\HandleSalereport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\HandlesProductDataDatabase;
use App\Traits\HandleProductHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{
     use HandlesProductDataDatabase;
   use HandleProductHistory;
   use HandleAccount;
   use HandleSalereport;

    public function index(Request $request, $branch)
{

     $request->merge(['branch_id' => $branch]);

    return $this->getSalesReport($request);
    // // You now have access to the branch in the controller
    // return Inertia::render('Supervisor/SupervisorHome', [
    //     'branch' => $branch
    // ]);
}
  
     public function product(Request $request, $branch)
    {
        $data = $this->getProductData($request);
        return Inertia::render('Admin/Product', array_merge($data, [
        'branch' => $branch,
    ]));
    }

   public function history(Request $request, $branch)
    {
        $data = $this->getProductHistory($request);
         return Inertia::render('Admin/ProductHistory', array_merge($data, [
        'branch' => $branch,
    ]));
    }

    public function account(Request $request, $branch)
    {
        $data = $this->getAccountData($request);
         return Inertia::render('Auth/Register', array_merge($data, [
        'branch' => $branch,
    ]));
    }
    public function cashier(Request $request, $branch)
    {
        // You can implement the logic for the cashier view here
        // For now, just returning a basic view
     return Inertia::render('Cashier/CashierHome', [
    'branch' => $branch,
]);

    }

       public function transfer(Request $request, $branch)
    {
        // You can implement the logic for the cashier view here
        // For now, just returning a basic view
     return Inertia::render('Admin/TransferProduct', [
    
        'branches' => AddBranchModel::select('id', 'branch_name')->get()
]);

    }

 public function salesreport(Request $request, $branch)
{
     $request->merge(['branch_id' => $branch]);

    return $this->getSalesReport($request);
}

public function expenses(Request $request)
{
    $user = $request->user();
    $userBranchId = $user->branch_id;

   

    $branches = AddBranchModel::select('id','branch_name')
        ->where('status', 1)
        ->orderBy('branch_name')->get();

    $Expenses = ExpensesModel::where('branch_id', $userBranchId)
        ->with('branch')
        ->latest()
        ->paginate(50);

    return Inertia::render('Supervisor/SupervisorExpenses', [
        'branches' => $branches,
        'Expenses' => $Expenses,
        'branchId' => (int) $userBranchId,
    ]);
}



public function storeexpenses(Request $request, $branch)
{
    if (!AddBranchModel::where('id', $branch)->exists()) {
        return back()->withErrors(['branch_id' => 'Branch not found.']);
    }

    $fields = $request->validate([
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric'
    ]);

    $fields['branch_id'] = $branch;

    ExpensesModel::create($fields);

    return back()->with('success', 'Expenses Added Successfully');
}
public function receive(Request $request, $branch)
{
    // 🔍 Fetch branch info
    $branchModel = AddBranchModel::where('branch_name', $branch)->first();
    if (!$branchModel) {
        abort(404, 'Branch not found');
    }

    // 📋 Base query with eager loading
    $transfersQuery = BranchTransferModel::with([
        'fromBranch:id,branch_name',
        'toBranch:id,branch_name',
        'product:id,description,product_code,account'
    ])
    ->where('to_branch_id', $branchModel->id);

    // 🔍 Apply search filter
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $transfersQuery->whereHas('product', function ($query) use ($searchTerm) {
            $query->where('description', 'like', "%{$searchTerm}%")
                  ->orWhere('product_code', 'like', "%{$searchTerm}%")
                  ->orWhere('account', 'like', "%{$searchTerm}%");
        });
    }

   if ($request->filled('date_from') && $request->filled('date_to')) {
    try {
        $from = Carbon::parse($request->date_from)->startOfDay();
        $to = Carbon::parse($request->date_to)->endOfDay();

        $transfersQuery->whereBetween('created_at', [$from, $to]);
    } catch (\Exception $e) {
        abort(400, 'Invalid date format');
    }
} else {
    // 🗓 Default to today's records if no date filter provided
    $today = Carbon::today();
    $transfersQuery->whereBetween('created_at', [$today->startOfDay(), $today->endOfDay()]);
}

    // 📦 Final paginated data
    $transfers = $transfersQuery->latest()->paginate(50)->withQueryString();

    // 🧾 Render view with filters
    return Inertia::render('Supervisor/RecieveBranch', [
        'branch' => $branch,
        'transfers' => $transfers,
        'filters' => $request->only(['search', 'date_from', 'date_to']),
    ]);
}
public function inventory(Request $request)
{
    $user = $request->user();
    $branchId = $user->branch_id;

    $product = ProductModel::with([
            'branchProducts' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            },
            'branchProducts.branch'
        ])
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('product_code', 'like', '%' . $request->search . '%');
            });
        })
        ->whereHas('branchProducts', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->select('id', 'product_code', 'description')
         ->paginate(50)
         ->withQueryString();

    return Inertia::render('Supervisor/Inventory', [
        'product' => $product,
        'searchTerm' => $request->search,
    ]);
}
public function updateInventory(Request $request)
{
    $user = $request->user();
    $branchId = $user->branch_id;

    $request->validate([
        'updates' => 'required|array',
        'updates.*.product_id' => 'required|integer|exists:product,id',
        'updates.*.quantity' => 'required|integer|min:0',
        'updates.*.quantity_status' => 'required|string',
    ]);

    DB::beginTransaction();

    try {
        foreach ($request->updates as $update) {
            $productId = $update['product_id'];
            $newQty = $update['quantity'];
            $status = $update['quantity_status'];

            // Update branch_products
            $branchProduct = BranchProductModel::where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->first();

            if ($branchProduct) {
                $branchProduct->quantity = $newQty;
                $branchProduct->save();

                // Check if branch_stock already exists
                $existingStock = BranchStockModel::where('product_id', $productId)
                    ->where('branch_id', $branchId)
                    ->first();

                if ($existingStock) {
                    // Update quantity_status
                    $existingStock->quantity_status = $status;
                    $existingStock->save();
                } else {
                    // Insert new record
                    BranchStockModel::create([
                        'branch_id' => $branchId,
                        'product_id' => $productId,
                        'quantity_status' => $status,
                    ]);
                }
            }
        }

        DB::commit();
        return redirect()->back()->with('success', 'Stock updated.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->withErrors(['error' => 'Failed to update inventory.']);
    }
}





 
}
