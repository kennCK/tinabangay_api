<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBrgyCodesChangeUrlColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('brgy_codes', function (Blueprint $table) {
        $table->dropColumn('url');
        $table->bigInteger('image_id')->after('region')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('brgy_codes', function (Blueprint $table) {
        //
      });
    }
}
