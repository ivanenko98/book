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
            $table->integer('folder_id', false, 10)->nullable();
            $table->string('name', 255);
            $table->text('content');
            $table->string('author', 255);
            $table->integer('likes')->default(0);
            $table->integer('percent')->default(0);
            $table->timestamps();
        });

        Schema::table('books', function($table) {
            $table->foreign('folder_id')->references('id')->on('folders');
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
