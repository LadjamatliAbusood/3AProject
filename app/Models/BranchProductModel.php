<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchProductModel extends Model
{
       protected $table = 'branch_products';
   protected $primaryKey = 'id';
   protected $fillable = ['branch_id','product_id','quantity','cost_price','retail_price','wholesale_price'];

   use HasFactory;

    public function product(){
  return $this->belongsTo(ProductModel::class, 'product_id');
    }
    public function branch(){
        return $this->belongsTo(AddBranchModel::class, 'branch_id');
    }
    public function transfersFrom()
{
    return $this->hasMany(BranchTransferModel::class, 'from_branch_id');
}

public function transfersTo()
{
    return $this->hasMany(BranchTransferModel::class, 'to_branch_id');
}

public function branchstock()
{
    return $this->hasOne(BranchStockModel::class, 'product_id', 'product_id')
        ->whereColumn('branch_id', 'branch_id');
}
// public function branchstock()
// {
//     return $this->hasMany(BranchStockModel::class, 'product_id');
//     // return $this->hasOne(BranchStockModel::class, 'product_id', 'product_id')
//     //     ->whereColumn('branch_id', 'branch_id');
// }



}
