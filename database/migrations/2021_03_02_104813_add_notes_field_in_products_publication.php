<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesFieldInProductsPublication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('notes')->after('quantity')->nullable();
        });

        Schema::table('vendor_products', function (Blueprint $table) {
            $table->text('notes')->after('quantity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('vendor_products', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}
