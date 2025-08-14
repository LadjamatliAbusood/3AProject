<?php

namespace App\Http\Controllers;

use App\Traits\HandleHome;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;


class AdminHomeController extends Controller
{
    use HandleHome;

    public function index(Request $request){

         
       return $this->getHomeData($request);
    }

//     public function index(Request $request)
// {
//     $dateFrom = $request->input('date_from') ?? Carbon::today()->toDateString();
//     $dateTo = $request->input('date_to') ?? Carbon::today()->toDateString();

//     $sales = DB::table('salesreport')
//         ->join('branch', 'salesreport.branch_id', '=', 'branch.id')
//         ->select(
//             'salesreport.branch_id',
//             'branch.branch_name',
//             DB::raw('SUM(quantity) as total_quantity'),
//             DB::raw('SUM(total_price) as total_sales'),
//             DB::raw('SUM(net_amount) as total_net_sales')
//         )
//         ->whereBetween('salesreport.created_at', [
//             Carbon::parse($dateFrom)->startOfDay(),
//             Carbon::parse($dateTo)->endOfDay(),
//         ])
//         ->groupBy('salesreport.branch_id', 'branch.branch_name')
//         ->get();

//     $chartData = $sales->map(function ($sale) {
//         return [
//             'name' => $sale->branch_name,
//             'quantity' => (int) $sale->total_quantity,
//             'sales' => (float) $sale->total_sales,
//             'income' => (float) $sale->total_net_sales,
//         ];
//     });

//     return Inertia::render('Admin/AdminHome', [
//         'chartData'    => $chartData,
//         'acct_roles'   => Auth::user()->acct_roles,
//         'branchTotals' => $sales,
//         'date_from'    => $dateFrom,
//         'date_to'      => $dateTo,
//     ]);
// }
}