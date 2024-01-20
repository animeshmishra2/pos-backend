<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_types', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idstore_type')->nullable();
            $table->string('name')->nullable();
            $table->double('cost')->nullable();
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
        Schema::drop('store_types');
    }
}
