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
        Schema::create('product_history', function (Blueprint $table) {
            $table->id();
            $table->string('account');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('category_id');
            $table->string('product_code');
            $table->string('description');         
            $table->decimal('cost_price',10,2);
            $table->decimal('retail_price',10,2);
            $table->decimal('wholesale_price',10,2);
            $table->integer('quantity');
            $table->tinyInteger('status');

            $table->timestamps();
             $table->foreign('branch_id')->references('id')->on('branch');
            $table->foreign('supplier_id')->references('id')->on('supplier');
             $table->foreign('category_id')->references('id')->on('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_history');
    }
};
