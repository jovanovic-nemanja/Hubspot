<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAgenciesAddAgencyPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agencies', function(Blueprint $table) {
            $table->boolean('agency_plan')->default(false)->after('paid_in_advance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agencies', function(Blueprint $table) {
            $table->dropColumn('agency_plan');
        });
    }
}
