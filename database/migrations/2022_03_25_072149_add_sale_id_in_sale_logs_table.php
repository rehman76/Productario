<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleIdInSaleLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_logs', function (Blueprint $table) {
            $table->dropColumn('error');
            $table->dropColumn('resource');
            $table->text('message')->change();
            $table->foreignId('sale_id')->nullable()->after('id')->references('id')->on('sales')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_logs', function (Blueprint $table) {
            $table->string('error');
            $table->string('resource');
            $table->string('message')->change();
            $table->dropForeign('sale_logs_sale_id_foreign');
        });
    }
}
