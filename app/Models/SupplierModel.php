<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierModel extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'id';
    protected $fillable = ['supplier_name', 'cost','status'];
    
    use HasFactory;

     public function product(){
        return $this->hasMany(ProductModel::class,'supplier_id');
    }
}
