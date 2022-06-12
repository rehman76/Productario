<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectorCouponRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connector_coupon_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connector_id')->nullable();
            $table->foreign('connector_id')->references('id')->on('connectors');
            $table->string('prefix')->nullable();
            $table->bigInteger('number_of_coupons')->nullable();
            $table->string('file_path')->nullable();
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
        Schema::dropIfExists('connector_coupon_requests');
    }
}
