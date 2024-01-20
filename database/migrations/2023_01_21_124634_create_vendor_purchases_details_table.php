<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorPurchasesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_purchases_details', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idvendor_purchases_detail')->nullable();
            $table->string('idproduct_master')->nullable();
            $table->double('mrp')->nullable();
            $table->string('hsn')->nullable();
            $table->double('quantity')->nullable();
            $table->double('unit_purchase_price')->nullable();
            $table->double('free_quantity')->nullable();
            $table->string('expiry')->nullable();
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
        Schema::drop('vendor_purchases_details');
    }
}
