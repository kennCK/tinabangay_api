<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RideHistory extends APIModel
{
  protected $table = 'ride_history';
  protected $fillable = ['account_id', 'from', 'from_date_time', 'to', 'to_date_time', 'type', 'code'];
}
