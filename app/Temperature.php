<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Temperature extends APIModel
{
  protected $table = 'temperatures';
  protected $fillable = ['account_id', 'added_by', 'value', 'remarks'];
}
