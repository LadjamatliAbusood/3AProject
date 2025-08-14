<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Models\ExpensesModel;
use App\Traits\HandleExpenses;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpensesController extends Controller
{
   
    use HandleExpenses;
    public function index(Request $request){

        return $this->getExpenses($request);

        // $branches = AddBranchModel::select('id','branch_name')
        // ->where('status', 1)
        // ->orderBy('branch_name')->get();;

        //  $Expenses = ExpensesModel::with('branch')->latest()->paginate(50);

        

        // return Inertia::render('Admin/Expenses',[
        //     'branches' => $branches,
        //     'Expenses' => $Expenses,
        // ]);    

    }
       public function store(Request $request){
        $fields = $request->validate([
            'branch_id' => 'required|numeric',
            'description' => 'required|string|max:255',
            'amount'=>'required|numeric'
            
        ]);

        $expenses = ExpensesModel::create($fields);
        return back()->with('success', 'Expenses Added Successfully');

        }

        public function update(Request $request, $id){
            $expenses = ExpensesModel::findOrFail($id);
            $fields = $request->validate([
            'branch_id' => 'required|numeric',
            'description' => 'required|string|max:255',
            'amount'=>'required|numeric'
            ]);

            $expenses->update($fields);
             return back()->with('success', 'Expenses updated Successfully');


        }
}
