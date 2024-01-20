<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('idsub_category')->nullable();
            $table->string('idcategory')->nullable();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->string('cat_icon')->nullable();
            $table->string('description')->nullable();
            $table->string('category_for')->nullable();
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
        Schema::drop('sub_categories');
    }
}
