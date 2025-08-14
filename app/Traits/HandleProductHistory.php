<?php

namespace App\Traits;

use App\Models\ProductHistoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

trait HandleProductHistory
{
    public function getProductHistory(Request $request)
{
    $user = $request->user();
    $branchId = $user->branch_id;
    $search = $request->search;

    $dateFrom = $request->filled('date_from')
        ? Carbon::parse($request->date_from)->startOfDay()
        : Carbon::today()->startOfDay();

    $dateTo = $request->filled('date_to')
        ? Carbon::parse($request->date_to)->endOfDay()
        : Carbon::today()->endOfDay();

    $query = ProductHistoryModel::with(['supplier', 'branch','category'])
        ->leftJoin('product as p', 'p.product_code', '=', 'product_history.product_code')
        ->leftJoin('branch_transfers as bt', function ($join) {
            $join->on('bt.product_id', '=', 'p.id')
                ->on('bt.quantity', '=', 'product_history.quantity');
        })
        ->leftJoin('branch as from_branch', 'from_branch.id', '=', 'bt.from_branch_id')
        ->leftJoin('branch as to_branch', 'to_branch.id', '=', 'bt.to_branch_id')
        ->select(
            'product_history.*',
            'bt.from_branch_id',
            'bt.to_branch_id',
            'from_branch.branch_name as from_branch_name',
            'to_branch.branch_name as to_branch_name'
        )
        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('product_history.product_code', 'like', "%{$search}%")
                    ->orWhere('product_history.description', 'like', "%{$search}%")
                    ->orWhere('product_history.account', 'like', "%{$search}%")
                    ->orWhereHas('branch', function ($branchQuery) use ($search) {
                        $branchQuery->where('branch_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('supplier_name', 'like', "%{$search}%");
                    })
                     ->orWhereHas('category', function ($CategoryQuery) use ($search) {
                        $CategoryQuery->where('category_name', 'like', "%{$search}%");
                    });
            });
        })
        ->whereBetween('product_history.created_at', [$dateFrom, $dateTo])
        ->when($user->acct_roles !== 1 && $user->acct_roles !== 2, function ($q) use ($branchId) {
            $q->where(function ($query) use ($branchId) {
                $query->where(function ($sub) use ($branchId) {
                    $sub->where('product_history.status', 4)
                        ->where('bt.from_branch_id', $branchId);
                })
                ->orWhere(function ($sub) use ($branchId) {
                    $sub->where('product_history.status', '!=', 4)
                        ->where('product_history.branch_id', $branchId);
                });
            });
        });

    $ProductHistory = $query->latest('product_history.created_at')->paginate(50)->withQueryString();

    $totalsQuery = (clone $query)->whereIn('product_history.status', [1, 2, 4]);
    $totalsQuery = $totalsQuery->where('product_history.quantity', '!=', -1);

    $totals = [
        'quantity' => $totalsQuery->sum('product_history.quantity'),
        'cost' => $totalsQuery->sum(DB::raw('product_history.cost_price * product_history.quantity')),
        'retail' => $totalsQuery->sum(DB::raw('product_history.retail_price * product_history.quantity')),
        'wholesale' => $totalsQuery->sum(DB::raw('product_history.wholesale_price * product_history.quantity')),
    ];

    return [
        'ProductHistory' => $ProductHistory,
        'totals' => $totals,
        'searchTerm' => $search,
        'date_from' => $dateFrom->toDateString(),
        'date_to' => $dateTo->toDateString(),
    ];
}

}