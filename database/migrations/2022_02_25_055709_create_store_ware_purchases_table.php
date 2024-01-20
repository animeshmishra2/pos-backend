<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreWarePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_ware_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idstore_warehouse')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('contact')->nullable();
            $table->string('is_copartner')->nullable();
            $table->string('is_store')->nullable();
            $table->string('name')->nullable();
            $table->string('pincode')->nullable();
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
        Schema::drop('store_ware_purchases');
    }
}
