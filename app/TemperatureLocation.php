<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemperatureLocation extends APIModel
{
  protected $table = 'temperature_locations';
  protected $fillable = ['account_id', 'temperature_id', 'longitude', 'latitude', 'route', 'locality', 'country', 'region'];

  public function temperature(){
    return $this->hasMany("App\Temperature" , "id" ,"temperature_id");
  }

}