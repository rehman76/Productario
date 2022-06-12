<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsIntoConnectorCouponCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('connector_coupon_requests', function (Blueprint $table) {
            $table->float('discount_percentage')->nullable()->after('file_path');
            $table->integer('max_usage')->nullable()->after('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('connector_coupon_requests', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
            $table->dropColumn('max_usage');
        });
    }
}
