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
        Schema::create('1c_pays', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('order_id')->nullable();
            $table->string('number')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->integer('sum')->nullable();
            $table->string('code')->nullable();
            $table->boolean('return')->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pays');
    }
};
