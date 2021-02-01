<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatewayTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gatewayTypes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('gateways_id');
            $table->integer('types_id');
            $table->boolean('is_support_deposit');
            $table->boolean('is_support_withdraw');
            $table->timestamps();
            $table->unique(['gateways_id','types_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gatewayTypes');
    }
}
