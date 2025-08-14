<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

trait HandleHome{

public function getHomeData(Request $request)
{
    $dateFrom = $request->input('date_from') ?? Carbon::today()->toDateString();
    $dateTo = $request->input('date_to') ?? Carbon::today()->toDateString();

    $dateRange = [
        Carbon::parse($dateFrom)->startOfDay(),
        Carbon::parse($dateTo)->endOfDay(),
    ];

    // 1. Get sales per branch
    $sales = DB::table('salesreport')
        ->join('branch', 'salesreport.branch_id', '=', 'branch.id')
        ->select(
            'salesreport.branch_id',
            'branch.branch_name',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(total_price) as total_sales'),
            DB::raw('SUM(net_amount) as total_net_sales')
        )
        ->whereBetween('salesreport.created_at', $dateRange)
        ->groupBy('salesreport.branch_id', 'branch.branch_name')
        ->get();

    // 2. Get expenses per branch
    $expensesByBranch = DB::table('expenses')
        ->select('branch_id', DB::raw('SUM(amount) as total_expenses'))
        ->whereBetween('created_at', $dateRange)
        ->groupBy('branch_id')
        ->get()
        ->keyBy('branch_id');

    // 3. Merge sales and expenses
    $mergedData = $sales->map(function ($sale) use ($expensesByBranch) {
        $branchId = $sale->branch_id;
        $expense = $expensesByBranch[$branchId]->total_expenses ?? 0;

        return (object)[
            'branch_id'        => $sale->branch_id,
            'branch_name'      => $sale->branch_name,
            'total_quantity'   => $sale->total_quantity,
            'total_sales'      => $sale->total_sales,
            'total_net_sales'  => $sale->total_net_sales,
            'total_expenses'   => $expense,
        ];
    });

    // 4. Prepare chart data
    $chartData = $mergedData->map(function ($item) {
        return [
            'name'     => $item->branch_name,
            'quantity' => (int) $item->total_quantity,
            'sales'    => (float) $item->total_sales,
            'income'   => (float) $item->total_net_sales,
            'expenses' => (float) $item->total_expenses,
        ];
    });
    // 5. Calculate overall totals
$totals = [
    'total_quantity'     => $mergedData->sum('total_quantity'),
    'total_sales_price'  => $mergedData->sum('total_sales'),
    'total_expenses'     => $mergedData->sum('total_expenses'),
    'total_net_amount'   => $mergedData->sum('total_net_sales'),
];

$highestSalesBranch = $mergedData->sortByDesc('total_sales')->first();
$lowestSalesBranch  = $mergedData->sortBy('total_sales')->first();

$highestNetBranch = $mergedData->sortByDesc('total_net_sales')->first();
$lowestNetBranch  = $mergedData->sortBy('total_net_sales')->first();

$categorySales = DB::table('salesreport as s')
    ->join('product as p', 's.product_id', '=', 'p.id')
    ->join('category as c', 'p.category_id', '=', 'c.id')
    ->join('branch as b', 's.branch_id', '=', 'b.id')
    ->select(
        'b.branch_name',
        'c.category_name',
        DB::raw('SUM(s.quantity) as total_quantity'),
        DB::raw('SUM(s.total_price) as total_sales'),
        DB::raw('SUM(s.net_amount) as total_net')
    )
    ->whereBetween('s.created_at', $dateRange) 
    ->groupBy('b.branch_name', 'c.category_name')
    ->orderBy('b.branch_name')
    ->orderByDesc('total_net')
    ->get();

    return Inertia::render('Admin/AdminHome', [
        'chartData'    => $chartData,
        'acct_roles'   => Auth::user()->acct_roles,
        'branchTotals' => $mergedData,
        'date_from'    => $dateFrom,
        'date_to'      => $dateTo,
        'totals' => $totals,
        'highestSalesBranch'  => $highestSalesBranch,
    'lowestSalesBranch'   => $lowestSalesBranch,
    'highestNetBranch'    => $highestNetBranch,
    'lowestNetBranch'     => $lowestNetBranch,
    'categorySales' => $categorySales,
        
    ]);
}

}