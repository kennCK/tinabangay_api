<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
class VisitedPlaceController extends APIController
{
  function __construct(){
    $this->model = new VisitedPlace();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); // store to $this->response['data'];
    // loop
    // check per route if existed on affected areas
    return $this->response();
  }

}
