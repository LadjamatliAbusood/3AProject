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
        Schema::create('salesreport', function (Blueprint $table) {
            $table->id();
            
             $table->string('account');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id')->nullable();
             $table->string('product_code');
            $table->string('description');
            $table->unsignedTinyInteger('selling_type')->default(1); // 1 for retail, 2 for wholesale
            $table->decimal('cost_price',10,2);
            $table->decimal('selling_price',10,2);
            $table->integer('quantity');
            $table->decimal('total_price',10,2);
            $table->decimal('net_amount',10,2);



            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('product')->onDelete('set null');;
            $table->foreign('branch_id')->references('id')->on('branch');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesreport');
    }
};
