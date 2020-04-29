<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkedAccount extends APIModel
{
  protected $table = 'linked_accounts';
  protected $fillable = ['account_id', 'owner'];
}
