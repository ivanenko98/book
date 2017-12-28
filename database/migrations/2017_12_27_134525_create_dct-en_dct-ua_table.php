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
        Schema::create('dct-en_dct-ua', function (Blueprint $table) {
            $table->increments('id');
            $table->text('en-id');
            $table->text('ua-id');
            $table->integer('user_id', false, 10)->nullable();
            $table->timestamps();
        });

        Schema::table('dct-en_dct-ua', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
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
