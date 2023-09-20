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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('course_id')->nullable();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('url')->nullable();
            $table->string('type_code')->nullable();
            $table->string('has_date')->nullable();
            $table->string('opened_at')->nullable();
            $table->boolean('is_new')->nullable();
            $table->integer('price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
