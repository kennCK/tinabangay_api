<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Symptom extends APIModel
{
  protected $table = 'symptoms';
  protected $fillable = ['account_id', 'type', 'remarks'];
}
