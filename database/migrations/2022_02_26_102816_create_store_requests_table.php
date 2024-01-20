<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('dispatch_date')->nullable();
            $table->string('dispatch_detail')->nullable();
            $table->string('dispatched_by')->nullable();
            $table->string('idstore_request')->nullable();
            $table->string('idstore_warehouse')->nullable();
            $table->string('status')->nullable();
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
        Schema::drop('store_requests');
    }
}
