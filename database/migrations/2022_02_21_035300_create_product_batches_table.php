<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idproduct_master')->nullable();
            $table->double('purchase_price')->nullable();
            $table->double('selling_price')->nullable();
            $table->double('mrp')->nullable();
            $table->double('discount')->nullable();
            $table->double('quantity')->nullable();
            $table->date('expiry')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('status')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product_batches');
    }
}
