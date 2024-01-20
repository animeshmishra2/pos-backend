<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_request_details', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idproduct_master')->nullable();
            $table->string('idstore_request')->nullable();
            $table->string('idstore_request_detail')->nullable();
            $table->string('quantity')->nullable();
            $table->string('quantity_sent')->nullable();
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
        Schema::drop('store_request_details');
    }
}
