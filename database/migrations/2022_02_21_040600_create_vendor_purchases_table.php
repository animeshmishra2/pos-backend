<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idvendor')->nullable();
            $table->string('idstore_warehouse')->nullable();
            $table->string('idproduct_master')->nullable();
            $table->double('purchase_price')->nullable();
            $table->double('mrp')->nullable();
            $table->double('selling_price')->nullable();
            $table->string('hsn')->nullable();
            $table->double('quantity')->nullable();
            $table->double('cgst')->nullable();
            $table->double('sgst')->nullable();
            $table->double('total_bill')->nullable();
            $table->double('paid')->nullable();
            $table->double('balance')->nullable();
            $table->date('mfg')->nullable();
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
        Schema::drop('vendor_purchases');
    }
}
