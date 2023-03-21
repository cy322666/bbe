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
        Schema::create('telegram_proxies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('status')->default(0);
            $table->json('body');
            $table->text('error')->nullable();
            $table->string('referrer')->nullable();
            $table->integer('lead_id')->nullable();
            $table->integer('contact_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_proxies');
    }
};
