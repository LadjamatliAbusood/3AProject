<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Models\ExpensesModel;
use App\Models\SalesReportModel;
use App\Traits\HandlesProductDataDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

public function exp(Request $request)
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

    return Inertia::render('Cashier/CashierExpenses', [
        'branches' => $branches,
        'Expenses' => $Expenses,
        'branchId' => (int) $userBranchId,
    ]);
}



public function storeexp(Request $request, $branch)
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

 public function sales(Request $request)
    {
        $user = $request->user();
        $acctRole = $user->acct_roles;
        $userBranchId = $user->branch_id;
        $selectedBranchId = $request->branch_id;

        $search = $request->search;
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : Carbon::today()->startOfDay();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::today()->endOfDay();

        $isSupervisorRoute = str_contains($request->path(), 'supervisor');

        // Sales report data
        $salesreport = SalesReportModel::with('branch','product.category')
            ->join('branch', 'salesreport.branch_id', '=', 'branch.id')
            ->when($isSupervisorRoute, function ($query) use ($userBranchId) {
                $query->where('salesreport.branch_id', $userBranchId);
            }, function ($query) use ($acctRole, $selectedBranchId, $userBranchId) {
                if (in_array($acctRole, [1, 2])) {
                    if (!empty($selectedBranchId)) {
                        $query->where('salesreport.branch_id', $selectedBranchId);
                    }
                } else {
                    $query->where('salesreport.branch_id', $userBranchId);
                }
            })
            ->whereBetween('salesreport.created_at', [$dateFrom, $dateTo])
            ->where(function ($q) use ($search) {
                $q->when($search, function ($query) use ($search) {
                    $query->where('salesreport.description', 'like', "%{$search}%")
                        ->orWhere('salesreport.product_code', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($branchQuery) use ($search) {
                            $branchQuery->where('branch_name', 'like', "%{$search}%");
                        })
                          ->orWhereHas('product.category', function ($catQuery) use ($search) {
                    $catQuery->where('category_name', 'like', "%{$search}%");
                });
                });
            })
            ->select('salesreport.*', 'branch.branch_name')
            ->orderBy('salesreport.created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Sales totals
        $salesTotals = SalesReportModel::join('branch', 'salesreport.branch_id', '=', 'branch.id')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('salesreport.description', 'like', "%$search%")
                        ->orWhere('salesreport.product_code', 'like', "%$search%")
                        ->orWhere('branch.branch_name', 'like', "%$search%");
                });
            })
            ->when($isSupervisorRoute, function ($query) use ($userBranchId) {
                $query->where('salesreport.branch_id', $userBranchId);
            }, function ($query) use ($acctRole, $selectedBranchId, $userBranchId) {
                if (in_array($acctRole, [1, 2]) && !empty($selectedBranchId)) {
                    $query->where('salesreport.branch_id', $selectedBranchId);
                } elseif (!in_array($acctRole, [1, 2])) {
                    $query->where('salesreport.branch_id', $userBranchId);
                }
            })
            ->whereBetween('salesreport.created_at', [$dateFrom, $dateTo])
            ->selectRaw('
                COALESCE(SUM(salesreport.quantity), 0) AS total_quantity,
                COALESCE(SUM(salesreport.total_price), 0) AS total_price,
                COALESCE(SUM(salesreport.net_amount), 0) AS net_amount
            ')
            ->first();

        // Expense totals
        $expenseQuery = DB::table('expenses')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        // Apply branch logic to expenses
        if ($isSupervisorRoute) {
            $expenseQuery->where('branch_id', $userBranchId);
        } elseif (in_array($acctRole, [1, 2]) && !empty($selectedBranchId)) {
            $expenseQuery->where('branch_id', $selectedBranchId);
        } elseif (!in_array($acctRole, [1, 2])) {
            $expenseQuery->where('branch_id', $userBranchId);
        }

        $totalExpenses = $expenseQuery->sum('amount');

        $totals = [
            'total_quantity' => $salesTotals->total_quantity ?? 0,
            'total_price' => $salesTotals->total_price ?? 0,
            'net_amount' => $salesTotals->net_amount ?? 0,
            'total_expenses' => $totalExpenses ?? 0,
        ];

        $branches = in_array($acctRole, [1, 2])
            ? AddBranchModel::select('id', 'branch_name')->get()
            : [];



        return Inertia::render('Cashier/CashierSales', [
            'salesreport' => $salesreport,
            'searchTerm' => $search,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'totals' => $totals,
            'branches' => $branches,
           

            
            
        ]);
        
    }




}
