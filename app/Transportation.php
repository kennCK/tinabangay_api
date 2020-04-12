<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transportation extends APIModel
{
  protected $table = 'transportations';
  protected $fillable = ['account_id', 'number', 'type', 'model'];
  
  public function userInfo(){
    return $this->hasMany('App\UserInformation', 'account_id', 'account_id');
  }

  public function rides(){
    return $this->belongsTo('App\Ride', 'transportation_id');
  }
}
