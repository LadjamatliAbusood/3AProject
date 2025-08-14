<?php

namespace App\Traits;

use App\Models\AddBranchModel;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

trait HandleAccount{

    public function getAccountData(Request $request)
    {
          
          
        $Account = User::with('branch')->latest()->paginate(50);
        $branches = AddBranchModel::select('id','branch_name')
        ->where('status', 1)
        ->orderBy('branch_name')->get();
        return 
        [
            'Account' => $Account,
            'branches' => $branches,
        ];
      

        }
}