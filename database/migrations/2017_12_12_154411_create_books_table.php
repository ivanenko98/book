<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->text('description');
            $table->string('author', 255);
            $table->integer('likes')->default(0);
            $table->integer('percent')->default(0);
            $table->integer('folder_id', false, 10)->nullable();
            $table->integer('user_id', false, 10)->nullable();
            $table->timestamps();
        });

        Schema::table('books', function($table) {
            $table->foreign('folder_id')->references('id')->on('folders');
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
        Schema::dropIfExists('books');
    }
}
