<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMlCommisionsColoumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('ml_commissions')->nullable();
            $table->decimal('taxes')->nullable();
            $table->decimal('expense')->nullable();
            $table->decimal('shipping_cost')->nullable();
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
            $table->dropColumn('ml_commissions');
            $table->dropColumn('taxes');
            $table->dropColumn('expense');
            $table->dropColumn('shipping_cost');
        });
    }
}
