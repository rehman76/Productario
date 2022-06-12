<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameApiAccessTokenTableToConnectors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('api_access_tokens', 'connectors');

        Schema::table('connectors', function (Blueprint $table) {
          $table->renameColumn('api_name', 'connector');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('connectors', function (Blueprint $table) {
            Schema::rename('connectors', 'api_access_tokens');

            Schema::table('connectors', function (Blueprint $table) {
                $table->renameColumn('connector', 'api_name');
            });
        });
    }
}
