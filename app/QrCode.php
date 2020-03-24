<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QrCode extends APIModel
{
  protected $table = 'qr_codes';
  protected $fillable = ['url', 'account_id'];
}
