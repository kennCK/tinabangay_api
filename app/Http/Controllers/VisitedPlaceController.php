<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
class VisitedPlaceController extends APIController
{
  public $tracingPlaceController = 'App\Http\Controllers\TracingPlaceController';
  
  function __construct(){
    $this->model = new VisitedPlace();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); // store to 
    $data = $this->response['data'];
    $i = 0;
    foreach ($data as $key => $value) {
      $this->response['data'][$i]['status'] = app($this->tracingPlaceController)->getStatus($data[$i]);
      $i++;
    }
    return $this->response();
  }

}
