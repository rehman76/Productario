<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->integer('sale_item_snapshot_id')->nullable();
            $table->boolean('is_publish')->default(0);
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->string('mla_id');
            $table->string('title');
            $table->integer('qty');
            $table->decimal('sale_fee')->default(0);
            $table->decimal('unit_price')->default(0);
            $table->decimal('full_unit_price')->default(0);
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
        Schema::dropIfExists('sale_items');
    }
}
