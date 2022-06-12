<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPropertiesInSaleItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('vendor_product_id')->nullable()->after('sale_id');
            $table->foreign('vendor_product_id')->references('id')->on('vendor_products');
            $table->foreignId('publication_id')->nullable()->after('vendor_product_id');
            $table->foreign('publication_id')->references('id')->on('publications');
            $table->json('publication_snapshot')->nullable()->after('publication_id');
            $table->json('vendor_product_snapshot')->nullable()->after('publication_snapshot');
            $table->float('publication_sale_price')->unsigned()->nullable()->after('vendor_product_snapshot');
            $table->float('vendor_product_cost')->unsigned()->nullable()->after('publication_sale_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['vendor_product_id']);
            $table->dropColumn('vendor_product_id');
            $table->dropForeign(['publication_id']);
            $table->dropColumn('publication_id');
            $table->dropColumn('publication_snapshot');
            $table->dropColumn('vendor_product_snapshot');
            $table->dropColumn('publication_sale_price');
            $table->dropColumn('vendor_product_cost');
        });
    }
}
