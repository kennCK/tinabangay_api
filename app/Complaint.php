<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Complaint extends APIModel
{
  protected $table = 'complaints';
  protected $fillable = ['code', 'message', 'status'];
}
