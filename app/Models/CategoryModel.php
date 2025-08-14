<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    
    protected $table = 'category';
    protected $primaryKey = 'id';
    protected $fillable = ['category_name','status'];

    use HasFactory;

    public function product(){
        return $this->hasMany(ProductModel::class, 'category_id');
    }

    public function history(){
        return $this->hasMany(ProductHistoryModel::class. 'category_id');
    }
}
