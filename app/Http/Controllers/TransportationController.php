<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transportation;
class TransportationController extends APIController
{
  function __construct(){
    $this->model = new Transportation();
  }

  public function getByParams($column, $value){
    $result = Transportation::where($column, '=', $value)->get();
    return (sizeof($result) > 0) ? $result[0] : null;
  }
}
