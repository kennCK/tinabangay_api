<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRidesTableAddVersion2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->bigInteger('owner_id')->nullable()->change();
            $table->bigInteger('transportation_id')->nullable()->change();
            $table->string('payload')->after('account_id');
            $table->longText('from')->after('transportation_id')->nullable();
            $table->dateTime('from_date_time')->after('from')->nullable();
            $table->longText('to')->after('from_date_time')->nullable();
            $table->dateTime('to_date_time')->after('to')->nullable();
            $table->string('type')->after('to_date_time')->nullable();
            $table->string('code')->after('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rides', function (Blueprint $table) {
            //
        });
    }
}
