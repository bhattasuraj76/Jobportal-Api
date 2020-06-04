<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->text('description')->nullable();
            $table->string('api_key')->nullable()->unique();
            $table->rememberToken();
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
        Schema::dropIfExists('employers');
    }
}
