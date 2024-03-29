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
        Schema::table('sites', function (Blueprint $table) {

            $table->integer(  'course_id')->nullable();
            $table->boolean(    'is_test')->default(false);
            $table->string(    'amount')->nullable();
            $table->string(    'course')->nullable();
            $table->string(    'product')->nullable();
            $table->string(    'action')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {

            $table->dropColumn('course_id');
            $table->dropColumn(  'is_test');
            $table->dropColumn( 'amount');
            $table->dropColumn( 'course');
            $table->dropColumn( 'product');
        });
    }
};
