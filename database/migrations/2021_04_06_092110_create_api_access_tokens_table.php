<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('api_name')->nullable();
            $table->text('access_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->string('scope')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('api_id')->nullable();
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
        Schema::dropIfExists('api_access_tokens');
    }
}
