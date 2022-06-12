<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeleteOnCascadeForProductInSyncLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            $table->dropForeign('sync_logs_product_id_foreign');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            $table->dropForeign('sync_logs_product_id_foreign');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }
}
