<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnImageBase64 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('publication_marketing_images', function (Blueprint $table) {
            $table->renameColumn('image_base64', 'image_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('publication_marketing_images', function (Blueprint $table) {
            $table->renameColumn('image_url', 'image_base64');
        });
    }
}
