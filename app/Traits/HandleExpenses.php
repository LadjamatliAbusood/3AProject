<?php

namespace App\Traits;

use App\Models\AddBranchModel;
use App\Models\ExpensesModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

Trait HandleExpenses{

    public function getExpenses(Request $request){
          $branches = AddBranchModel::select('id','branch_name')
        ->where('status', 1)
        ->orderBy('branch_name')->get();;

         $Expenses = ExpensesModel::with('branch')->latest()->paginate(50);

        

        return Inertia::render('Admin/Expenses',[
            'branches' => $branches,
            'Expenses' => $Expenses,
        ]); 
    }
}