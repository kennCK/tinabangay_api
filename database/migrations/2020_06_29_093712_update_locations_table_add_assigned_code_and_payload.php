<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLocationsTableAddAssignedCodeAndPayload extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('locations', function (Blueprint $table) {
      $table->string('assigned_code')->after('code')->nullable();
      $table->string('payload')->after('assigned_code')->nullable();
      $table->string('longitude')->nullable()->change();
      $table->string('latitude')->nullable()->change();
      $table->string('route')->nullable()->change();
      $table->string('locality')->nullable()->change();
      $table->string('country')->nullable()->change();
      $table->string('region')->nullable()->change();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('locations', function (Blueprint $table) {
      //
    });
  }
}
