<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManadatoryAndStatusColoumnsInTnCheckoutUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tn_checkout_urls', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('params');
            $table->string('contact_last_name')->nullable()->after('contact_name');
            $table->string('contact_email')->nullable()->after('contact_last_name');
            $table->boolean('is_active')->default(1)->after('title');
            $table->dropColumn('coupon');
            $table->double('discount_percentage', 8, 2)->nullable()->after('url');
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
            $table->dropColumn('contact_name');
            $table->dropColumn('contact_last_name');
            $table->dropColumn('contact_email');
            $table->dropColumn('is_active');
            $table->dropColumn('discount_percentage');
            $table->string('coupon')->nullable();
        });
    }
}
