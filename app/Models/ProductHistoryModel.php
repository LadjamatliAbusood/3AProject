<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductHistoryModel extends Model
{
   protected $table = 'product_history';
    protected $primaryKey = 'id';
    protected $fillable = ['account','branch_id','supplier_id','category_id','product_code','description','cost_price','retail_price','wholesale_price','quantity','status'];

    use HasFactory;

     public function supplier(){
        return $this->belongsTo(SupplierModel::class,'supplier_id');
    }
    
     public function branch(){
        return $this->belongsTo(AddBranchModel::class,'branch_id');
    }
    public function fromBranch()
{
    return $this->belongsTo(AddBranchModel::class, 'from_branch_id');
}

public function toBranch()
{
    return $this->belongsTo(AddBranchModel::class, 'to_branch_id');
}

public function product()
{
    return $this->belongsTo(ProductModel::class, 'product_id');
}
public function category(){
    return $this->belongsTo(CategoryModel::class, 'category_id');
}


}
