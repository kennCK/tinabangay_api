<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Patient extends APIModel
{
  protected $table = 'patients';
  protected $fillable = ['account_id', 'added_by', 'status', 'remarks'];
  public function userInfo(){
    return $this-> hasOne('App\UserInformation','account_id','account_id');
  }
}
