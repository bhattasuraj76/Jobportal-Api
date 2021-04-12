<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->string('level')->nullable();
            $table->string('experience')->nullable();
            $table->string('expiry_date')->nullable();
            $table->string('salary')->nullable();
            $table->string('qualification')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('employer_id')->unsigned();
            $table->foreign('employer_id')->references('id')->on('employers')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
