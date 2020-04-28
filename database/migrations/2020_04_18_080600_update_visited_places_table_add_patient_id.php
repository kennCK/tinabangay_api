<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateVisitedPlacesTableAddPatientId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visited_places', function (Blueprint $table) {
            $table->bigInteger('account_id')->after('id')->nullable();
            $table->bigInteger('patient_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visited_places', function (Blueprint $table) {
            //
        });
    }
}
