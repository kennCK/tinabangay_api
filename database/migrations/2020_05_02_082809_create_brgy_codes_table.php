<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrgyCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brgy_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('longitude');
            $table->string('latitude');
            $table->string('route');
            $table->string('locality');
            $table->string('country');
            $table->string('region');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brgy_codes');
    }
}
