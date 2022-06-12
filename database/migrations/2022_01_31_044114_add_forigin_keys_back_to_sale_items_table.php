<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForiginKeysBackToSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreign('publication_id')->references('id')->on('publications')->onDelete('set null');
            $table->foreign('vendor_product_id')->references('id')->on('vendor_products')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
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
            $table->dropForeign('sale_items_publication_id_foreign');
            $table->dropForeign('sale_items_vendor_product_id_foreign');
            $table->dropForeign('sale_items_vendor_id_foreign');
        });
    }
}
