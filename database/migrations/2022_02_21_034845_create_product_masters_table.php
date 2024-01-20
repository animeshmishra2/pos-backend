<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_masters', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idcategory')->nullable();
            $table->string('idsub_category')->nullable();
            $table->string('idsub_sub_category')->nullable();
            $table->double('idbrand')->nullable();
            $table->string('purchase_price')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('barcode')->nullable();
            $table->string('hsn')->nullable();
            $table->double('cgst')->nullable();
            $table->double('sgst')->nullable();
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
        Schema::drop('product_masters');
    }
}
