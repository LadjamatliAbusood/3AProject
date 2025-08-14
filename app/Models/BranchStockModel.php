<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchStockModel extends Model
{
    protected $table = 'branch_stock';
   protected $primaryKey = 'id';
   protected $fillable = ['branch_id','product_id','quantity_status'];


    public function BranchProduct(){
        return $this->belongsTo(BranchProductModel::class,'branch_id');
    }
      public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
