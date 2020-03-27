<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ride extends APIModel
{
    protected $table = 'rides';
    protected $fillable = ['account_id', 'owner_id', 'transportation_id'];
}
