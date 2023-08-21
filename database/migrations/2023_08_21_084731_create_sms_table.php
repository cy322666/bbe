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
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('send_code')->nullable();
            $table->integer('get_code')->nullable();
            $table->string('id_sms')->nullable();
            $table->string('status')->nullable();
            $table->string('info')->nullable();
            $table->string('result')->nullable();
            $table->text('error')->nullable();
            $table->integer('lead_id')->nullable();
            $table->string('phone')->nullable();
            $table->integer('contact_id')->nullable();
            $table->boolean('is_agreement')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
};
