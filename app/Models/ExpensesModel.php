<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensesModel extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'id';
    protected $fillable = ['branch_id','description','amount'];

    use HasFactory;


    public function branch(){
       return $this->belongsTo(AddBranchModel::class, 'branch_id');
    }
}
