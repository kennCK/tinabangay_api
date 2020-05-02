<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Location;
class LocationController extends APIController
{
  function __construct(){
    $this->model = new Location();
    $this->notRequired = array(
      'code'
    );
  }

  public function getByParamsWithCode($column, $value){
    $result = Location::where($column, '=', $value)->where('code', '!', null)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }
}
