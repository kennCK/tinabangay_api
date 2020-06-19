<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthDeclaration extends APIModel
{
  protected $table = 'health_declarations';
  protected $fillable = ['account_id', 'code', 'owner', 'content'];
}
