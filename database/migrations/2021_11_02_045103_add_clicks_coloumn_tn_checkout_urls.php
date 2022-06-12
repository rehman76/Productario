<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClicksColoumnTnCheckoutUrls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tn_checkout_urls', function (Blueprint $table) {
            $table->integer('clicks')->default(0)->after('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tn_checkout_urls', function (Blueprint $table) {
            $table->dropColumn('clicks');
        });
    }
}
