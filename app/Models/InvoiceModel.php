<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceModel extends Model
{
     protected $table = 'invoice';
   protected $primaryKey = 'id';
   protected $fillable = ['branch_id','invoice_number','pdf_path'];

   use HasFactory;

 
    public function branch(){
        return $this->belongsTo(AddBranchModel::class, 'branch_id');
    }
}
