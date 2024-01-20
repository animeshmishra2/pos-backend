<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCounterTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('counter_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idcounters_login')->nullable();
            $table->double('amount')->nullable();
            $table->string('is_inbound')->nullable();
            $table->string('details')->nullable();
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
        Schema::drop('counter_transactions');
    }
}
