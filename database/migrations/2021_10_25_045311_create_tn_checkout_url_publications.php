<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTnCheckoutUrlPublications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tn_checkout_url_publications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tn_checkout_url_id');
            $table->unsignedBigInteger('publication_id');
            $table->foreign('tn_checkout_url_id')->references('id')->on('tn_checkout_urls')->onDelete('cascade');
            $table->foreign('publication_id')->references('id')->on('publications')->onDelete('cascade');
            $table->text('publication_name')->nullable();
            $table->integer('qty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tn_checkout_url_publications');
    }
}
