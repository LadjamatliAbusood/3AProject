<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
                 $table->id();
            $table->unsignedBigInteger('branch_id');
     
            $table->string('invoice_number')->unique();
            $table->string('pdf_path');
            $table->timestamps();

           
             $table->foreign('branch_id')->references('id')->on('branch')->onDelete('cascade');
     
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
