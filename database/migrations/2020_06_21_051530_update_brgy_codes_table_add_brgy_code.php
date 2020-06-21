<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBrgyCodesTableAddBrgyCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('brgy_codes', function (Blueprint $table) {
        $table->string('brgy_code')->after('code')->nullable();
        $table->string('code')->nullable()->change();
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
