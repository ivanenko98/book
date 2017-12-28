<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDctEnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dct-en', function (Blueprint $table) {
            $table->increments('id');
            $table->text('word');
            $table->integer('user_id', false, 10)->nullable();
            $table->timestamps();
        });

        Schema::table('dct-en', function($table) {
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
        Schema::dropIfExists('dct-en');
    }
}
