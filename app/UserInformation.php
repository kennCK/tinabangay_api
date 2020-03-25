<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInformation extends APIModel
{
    protected $table = 'account_informations';
    protected $fillable = ['account_id','first_name','middle_name', 'last_name', 'birth_date', 'sex', 'cellular_number', 'address', 'created_at', 'updated_at', 'deleted_at'];
}
