<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountersLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('counters_logins', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idcounter')->nullable();
            $table->string('idstaff')->nullable();
            $table->double('open_balance')->nullable();
            $table->double('close_balance')->nullable();
            $table->string('open_cash_detail')->nullable();
            $table->string('close_cash_detail')->nullable();
            $table->double('online_payments')->nullable();
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
        Schema::drop('counters_logins');
    }
}
