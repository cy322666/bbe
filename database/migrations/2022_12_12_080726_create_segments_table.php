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
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->json('body')->nullable();
            $table->integer('lead_id')->nullable();
            $table->integer('status')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('sale')->default(0);
            $table->integer('count_leads')->default(1);
            $table->text('error')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('segments');
    }
};
