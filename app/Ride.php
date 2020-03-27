<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ride extends APIModel
{
    protected $table = 'rides';
    protected $fillable = ['account_id', 'owner_id', 'transportation_id', 'payload', 'from', 'from_date_time', 'to', 'to_date_time', 'type', 'date'];
}
