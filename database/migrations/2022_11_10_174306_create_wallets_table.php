<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idwallet')->nullable();
            $table->string('idcustomer')->nullable();
            $table->string('type')->nullable();
            $table->string('ref_id')->nullable();
            $table->float('amount')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('remark')->nullable();
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
        Schema::drop('wallets');
    }
}
