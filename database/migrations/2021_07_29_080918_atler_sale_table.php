<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AtlerSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->bigInteger('order_id')->nullable();
            $table->string('status')->nullable();
            $table->decimal('total_amount')->default(0);
            $table->decimal('profit')->default(0);
            $table->timestamp('date_created')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->dropColumn('status');
            $table->dropColumn('total_amount');
            $table->dropColumn('profit');
            $table->dropColumn('date_created');


        });
    }
}
