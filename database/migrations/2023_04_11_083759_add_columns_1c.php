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
        Schema::table('1c_pays', function (Blueprint $table) {

            $table->string('lead_id')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('check_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('1c_pays', function (Blueprint $table) {

            $table->dropColumn('lead_id');
            $table->dropColumn('contact_id');
            $table->dropColumn('check_id');
        });
    }
};
