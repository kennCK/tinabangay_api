<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends APIModel
{
  protected $table = 'locations';
  protected $fillable = ['account_id', 'longitude', 'latitude', 'route', 'locality', 'country', 'region', 'code', 'assigned_code', 'payload'];
}
