<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
  
    protected $table = 'product';
    protected $primaryKey = 'id';
    protected $fillable = ['supplier_id','category_id','account','product_code','barcode','description'];

    use HasFactory;

    public function supplier(){
        return $this->belongsTo(SupplierModel::class,'supplier_id');
    }
public function branchProducts()
{
    return $this->hasMany(BranchProductModel::class, 'product_id');
}
// public function Branch()
// {
//     return $this->belongsTo(AddBranchModel::class, 'branch_id');
// }

public function category(){
    return $this->belongsTo(CategoryModel::class, 'category_id');
}

public function history(){
    return $this->hasMany(ProductHistoryModel::class, 'product_id');
}

public function salesreport(){
    return $this->hasMany(SalesReportModel::class, 'product_id');
}
public function invoice(){
    return $this->hasMany(InvoiceModel::class, 'product_id');
}

public function branchstock(){
    return $this->hasMany(BranchStockModel::class, 'product_id');
}
}

