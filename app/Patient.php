<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Patient extends APIModel
{
  protected $table = 'patients';
  protected $fillable = ['account_id', 'code', 'added_by', 'status','source', 'remarks'];
  public function userInfo(){
    return $this-> hasOne('App\UserInformation','account_id','account_id');
  }
}
