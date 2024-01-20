<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStaffAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_accesses', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idstaff')->nullable();
            $table->string('idstore_warehouse')->nullable();
            $table->string('idaccess_level')->nullable();
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
        Schema::drop('staff_accesses');
    }
}
