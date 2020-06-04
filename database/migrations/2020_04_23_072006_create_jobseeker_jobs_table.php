<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobseekerJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobseeker_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('jobseeker_id')->unsigned();
            $table->foreign('jobseeker_id')->references('id')->on('jobseekers')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('job_id')->unsigned();
            $table->foreign('job_id')->references('id')->on('jobs')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('jobseeker_jobs');
    }
}
