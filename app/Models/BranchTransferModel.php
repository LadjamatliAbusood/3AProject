<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchTransferModel extends Model
{
 protected $table = 'branch_transfers';
   protected $primaryKey = 'id';
    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'product_id',
        'quantity',
        'transferred_by'
    ];

    use HasFactory;


    public function fromBranch(){
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
}
