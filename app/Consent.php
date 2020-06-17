<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consent extends APIModel
{
  protected $table = 'consents';
  protected $fillable = ['account_id', 'status'];
}
