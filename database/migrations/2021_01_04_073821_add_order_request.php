<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_request',  function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_id', 100);
            $table->tinyInteger('payment_type');
            $table->json('params')->default('(JSON_ARRAY())');
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
        Schema::dropIfExists('order_request');
    }
}
