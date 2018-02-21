<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreatePurchasedBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchased_books', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('buyer_id', false, 10)->nullable();
            $table->integer('seller_id', false, 10)->nullable();
            $table->integer('book_id', false, 10)->nullable();
            $table->string('price')->nullable();
            $table->string('status')->nullable()->default('available');
            $table->foreign('buyer_id')->references('id')->on('users');
            $table->foreign('seller_id')->references('id')->on('users');
            $table->foreign('book_id')->references('id')->on('books');
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
        Schema::dropIfExists('purchased_books');
    }
}