<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idpackage')->nullable();
            $table->string('idpackage_master')->nullable();
            $table->string('idstore_warehouse')->nullable();
            $table->string('applicable_on')->nullable();
            $table->string('frequency')->nullable();
            $table->string('base_trigger_amount')->nullable();
            $table->string('additional_tag_amount')->nullable();
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
        Schema::drop('packages');
    }
}
