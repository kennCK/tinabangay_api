<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends APIModel
{
  protected $table = 'notification_settings';
  protected $fillable = ['code', 'account_id', 'email_login', 'email_otp', 'sms_login', 'sms_otp'];

  public function getEmailLoginAttribute($value){
    return intval($value);
  }

  public function getEmailOtpAttribute($value){
    return intval($value);
  }

  public function getSmsLoginAttribute($value){
    return intval($value);
  }

  public function getSmsOtpAttribute($value){
    return intval($value);
  }

  public function getAccountIdAttribute($value){
    return intval($value);
  }
}
