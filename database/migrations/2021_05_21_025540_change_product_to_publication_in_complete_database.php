<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProductToPublicationInCompleteDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('images');

        Schema::table('product_category', function (Blueprint $table) {
            $table->dropForeign('product_category_product_id_foreign');
            $table->renameColumn('product_id','publication_id');
        });

        Schema::table('product_logs', function (Blueprint $table) {
            $table->dropForeign('product_logs_product_id_foreign');
            $table->renameColumn('product_id','publication_id');
        });

        Schema::drop('product_sale');

        Schema::table('product_vendor_product', function (Blueprint $table) {
            $table->dropForeign('product_vendor_product_product_id_foreign');
            $table->renameColumn('product_id','publication_id');
        });

        Schema::table('sync_logs', function (Blueprint $table) {
            $table->dropForeign('sync_logs_product_id_foreign');
            $table->renameColumn('product_id','publication_id');
        });

        Schema::rename('products', 'publications');
        Schema::rename('product_category', 'publication_category');
        Schema::rename('product_logs', 'publication_logs');
        Schema::rename('product_vendor_product', 'publication_vendor_product');

        Schema::table('publication_category', function (Blueprint $table) {
            $table->foreign('publication_id')->references('id')->on('publications')->onDelete('cascade');
        });

        Schema::table('publication_logs', function (Blueprint $table) {
            $table->foreign('publication_id')->references('id')->on('publications')->onDelete('cascade');;
        });

        Schema::table('publication_vendor_product', function (Blueprint $table) {
            $table->foreign('publication_id')->references('id')->on('publications')->onDelete('cascade');;
        });

        Schema::table('sync_logs', function (Blueprint $table) {
            $table->foreign('publication_id')->references('id')->on('publications')->onDelete('cascade');;
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('publication_in_complete_database', function (Blueprint $table) {
            //
        });
    }
}
