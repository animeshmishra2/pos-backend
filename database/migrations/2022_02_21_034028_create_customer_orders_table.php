<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomerOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idcustomer')->nullable();
            $table->string('is_online')->nullable();
            $table->string('is_pos')->nullable();
            $table->string('is_paid_online')->nullable();
            $table->string('is_paid')->nullable();
            $table->string('is_delivery')->nullable();
            $table->double('total_quantity')->nullable();
            $table->double('total_price')->nullable();
            $table->double('total_cgst')->nullable();
            $table->double('total_sgst')->nullable();
            $table->double('total_discount')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('promocode')->nullable();
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
        Schema::drop('customer_orders');
    }
}
