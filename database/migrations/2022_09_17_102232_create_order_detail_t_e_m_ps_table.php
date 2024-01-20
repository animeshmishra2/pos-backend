<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrderDetailTEMPsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_detail_t_e_m_ps', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idcustomer_order_temp')->nullable();
            $table->string('idproduct_master')->nullable();
            $table->string('idinventory')->nullable();
            $table->string('quantity')->nullable();
            $table->float('total_price')->nullable();
            $table->float('total_cgst')->nullable();
            $table->float('total_sgst')->nullable();
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
        Schema::drop('order_detail_t_e_m_ps');
    }
}
