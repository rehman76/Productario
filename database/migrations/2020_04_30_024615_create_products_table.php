<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable();
            $table->string('sku');
            $table->string('ean')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['waiting', 'running', 'failed', 'done'])->nullable();
            $table->integer('markup')->unsigned()->nullable();
            $table->float('discount')->unsigned()->nullable();
            $table->integer('woo_id')->unsigned()->nullable();
            $table->integer('mla')->nullable();
            $table->float('price')->unsigned()->nullable();
            $table->float('sale_price')->unsigned()->nullable();
            $table->integer('iva')->unsigned()->nullable();
            $table->float('other_taxes')->unsigned()->nullable();
            $table->integer('quantity');
            $table->integer('min_quantity')->nullable();
            $table->string('type')->nullable();
            $table->integer('parent_id')->nullable();
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('categories');
        });

        Schema::create('vendor_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('vendor_id');
            $table->string('sku')->nullable();
            $table->string('name')->nullable();
            $table->boolean('status')->nullable();
            $table->text('description')->nullable();
            $table->float('discount')->nullable();
            $table->string('ean')->nullable();
            $table->enum('currency',['USD','ARS'])->nullable();
            $table->string('link')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('min_quantity')->nullable();
            $table->double('price', 15,2);
            $table->float('sale_price')->nullable();
            $table->float('iva')->nullable();
            $table->float('other_taxes')->nullable();
            $table->float('weight')->nullable();   


            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('vendor_id')->references('id')->on('vendors');
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
        Schema::dropIfExists('vendor_products');
        Schema::dropIfExists('products');
    }
}
