<?php

namespace App\Http\Controllers;

use App\Models\ProductHistoryModel;
use App\Models\ProductModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Traits\HandleProductHistory;
class ProductHistoryController extends Controller
{

      use HandleProductHistory;
    public function index(Request $request)
    {
        $data = $this->getProductHistory($request);
        return inertia::render('Admin/ProductHistory', $data);
    }
//  public function index(Request $request)
// {
//     $search = $request->search;

//     // Default to today's date if not provided
//     $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : Carbon::today()->startOfDay();
//     $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::today()->endOfDay();

//     $query = ProductHistoryModel::with('supplier')
//         ->when($search, function ($q) use ($search) {
//             $q->where(function ($sub) use ($search) {
//                 $sub->where('product_code', 'like', "%{$search}%")
//                     ->orWhere('description', 'like', "%{$search}%")
//                     ->orWhere('account', 'like', "%{$search}%");
                    
                    
//             });
//         })
//         ->whereBetween('created_at', [$dateFrom, $dateTo]);

//     $ProductHistory = $query->latest()->paginate(20)->withQueryString();

//   $totalsQuery = (clone $query)->whereIn('status', [1, 2]);

//     $totals = [
//         'quantity' => $totalsQuery->sum('quantity'),
//         'cost' => $totalsQuery->sum(DB::raw('cost_price * quantity')),
//         'retail' => $totalsQuery->sum(DB::raw('retail_price * quantity')),
//         'wholesale' => $totalsQuery->sum(DB::raw('wholesale_price * quantity')),
//     ];

//     return Inertia::render('Admin/ProductHistory', [
//         'ProductHistory' => $ProductHistory,
//         'totals' => $totals,
//         'searchTerm' => $search,
//         'date_from' => $dateFrom->toDateString(),
//         'date_to' => $dateTo->toDateString(),
//     ]);
// }


}