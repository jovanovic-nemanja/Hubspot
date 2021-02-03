<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePortalsOauthExtended extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portals_oauth_extended', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('portal_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('oauth_provider_id')->index();
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
        Schema::dropIfExists('portals_oauth_extended');
    }
}
