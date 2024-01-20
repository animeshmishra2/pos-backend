<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idcustomer')->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->nullable();
            $table->string('landmark')->nullable();
            $table->string('is_default')->nullable();
            $table->string('phone')->nullable();
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
        Schema::drop('customer_addresses');
    }
}
