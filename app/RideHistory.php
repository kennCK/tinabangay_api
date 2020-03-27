<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RideHistory extends APIModel
{
  protected $table = 'ride_history';
  protected $fillable = ['account_id', 'from', 'to', 'type', 'code'];
}
