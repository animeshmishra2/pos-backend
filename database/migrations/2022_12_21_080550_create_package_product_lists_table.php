<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePackageProductListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_product_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idpackage_prod_list')->nullable();
            $table->string('idpackage')->nullable();
            $table->string('idproduct_master')->nullable();
            $table->double('quantity')->nullable();
            $table->string('is_triggerer_tag_along')->nullable();
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
        Schema::drop('package_product_lists');
    }
}
