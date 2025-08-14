<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReportModel extends Model
{
     protected $table = 'salesreport';
    protected $primaryKey = 'id';
    protected $fillable = ['account','branch_id','product_id','product_code','description','selling_price',
    'selling_type','quantity','cost_price','total_price','net_amount'];

    use HasFactory;

    public function branch()
{
    return $this->belongsTo(AddBranchModel::class, 'branch_id');
}

public function product(){
    return $this->belongsTo(ProductModel::class, 'product_id');
}

}
