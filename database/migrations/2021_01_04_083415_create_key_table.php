<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->bigInteger('gateway_id')->index();
            $table->bigInteger('user_pk')->index();
            $table->json('keys');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keys');
    }
}
