<?php

namespace App\Http\Controllers;

use App\Models\SalesReportModel;
use App\Traits\HandleSalereport;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    use HandleSalereport;
    public function index(Request $request)
{
    
    // $data = $this->getSalesReport($request);
    //     return inertia('Admin/SalesReport', $data);
    
    return $this->getSalesReport($request); 
    
}

public function destroy($id){
    
    $salesreport = SalesReportModel::findOrFail($id);
    $salesreport->delete();

    

    return redirect()->back()->with('success', 'salesreport deleted successfully.');

}
}
