<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddBranchModel extends Model
{
   protected $table = 'branch';
   protected $primaryKey = 'id';
   protected $fillable = ['branch_name','location','status'];

   use HasFactory;
    public function getRouteKeyName()
    {
         return 'branch_name';
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
     public function BranchProduct(){
        return $this->hasMany(BranchProductModel::class,'branch_id');
    }

    public function salesreport(){
  return $this->hasMany(SalesReportModel::class,'branch_id');
}

public function expenses(){
  return $this->hasMany(ExpensesModel::class, 'branch_id');
}
}
