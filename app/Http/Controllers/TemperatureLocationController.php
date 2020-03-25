<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TemperatureLocation;
class TemperatureLocationController extends APIController
{
  function __construct(){
    $this->model = new TemperatureLocation();
  }

  function retrieve(Request $request){

    $resp=   TemperatureLocation::with("temperature")->get();
    $this->retrieveDB($resp);
    return $this->response(); 
  }
}
