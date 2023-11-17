<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hubspot_sites', function (Blueprint $table) {

            $table->string(  'tg_nick')->nullable();
            $table->string(  'clientid')->nullable();
            $table->string(  'utm_source')->nullable();
            $table->string(  'utm_medium')->nullable();
            $table->string(  'utm_content')->nullable();
            $table->string(  'utm_campaign')->nullable();
            $table->string(  'utm_term')->nullable();
            $table->string(  'career_tariff')->nullable();
            $table->string(  'career_position')->nullable();
            $table->string(  'career_need')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hubspot_sites', function (Blueprint $table) {

            $table->dropColumn(  'tg_nick')->nullable();
            $table->dropColumn(  'clientid')->nullable();
            $table->dropColumn(  'utm_source')->nullable();
            $table->dropColumn(  'utm_medium')->nullable();
            $table->dropColumn(  'utm_content')->nullable();
            $table->dropColumn(  'utm_campaign')->nullable();
            $table->dropColumn(  'utm_term')->nullable();
            $table->dropColumn(  'career_tariff')->nullable();
            $table->dropColumn(  'career_position')->nullable();
            $table->dropColumn(  'career_need')->nullable();
        });
    }
};
