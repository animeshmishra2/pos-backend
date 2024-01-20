<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRateSlabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_slabs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idrate_slab')->nullable();
            $table->string('idpackage')->nullable();
            $table->double('from_amount')->nullable();
            $table->double('till_amount')->nullable();
            $table->double('additional_amount')->nullable();
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
        Schema::drop('rate_slabs');
    }
}
