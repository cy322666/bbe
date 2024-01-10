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
        Schema::create('broken_segments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->json('body')->nullable();
            $table->integer('vid')->nullable();
            $table->string('firstname')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('coursename')->nullable();
            $table->string('coursetype')->nullable();
            $table->string('course_url')->nullable();
            $table->string('courseid')->nullable();
            $table->integer('lead_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->string('submitted_at')->nullable();
            $table->integer('status')->default(0);
            $table->boolean('is_double')->nullable();
            $table->boolean('is_test')->nullable();
            $table->string('type_segment')->nullable();
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
