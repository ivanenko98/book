<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDctEnDctUaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dcten_dctua', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('en_id')->unsigned();
            $table->foreign('en_id')->references('id')->on('dcten');
            $table->integer('ua_id')->unsigned();
            $table->foreign('ua_id')->references('id')->on('dctua');
            $table->integer('user_id', false, 10)->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dct-en_dct-ua');
    }
}
