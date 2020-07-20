<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends APIModel
{
  protected $table = 'posts';
  protected $fillable = ['code', 'content'];
}
