<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTiendanubeVariantIdInTnCheckoutUrlPublicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tn_checkout_url_publications', function (Blueprint $table) {
            $table->string('tiendanube_variant_id')->after('publication_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tn_checkout_url_publications', function (Blueprint $table) {
            $table->dropColumn('tiendanube_variant_id');
        });
    }
}
