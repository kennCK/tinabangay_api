<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rides extends Model
{
    protected $table = 'rides';
    // account_id: id of the owner 
    protected $fillable = ['account_id', 'owner_id', 'transportation_id'];
}
