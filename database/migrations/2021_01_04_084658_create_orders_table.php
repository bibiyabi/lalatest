<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', '20')->unique();
            $table->string('user_order_id', '100');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('key_id');
            $table->decimal('amount', 20, 4, true);
            $table->decimal('real_amount', 20, 4, true);
            $table->unsignedBigInteger('gateway_id');
            $table->unsignedSmallInteger('status');
            $table->jsonb('order_param');
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
        Schema::dropIfExists('orders');
    }
}
