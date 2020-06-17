<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthDeclaration extends Model
{
  protected $table = 'health_declarations';
  protected $fillable = ['account_id', 'owner', 'content'];
}
