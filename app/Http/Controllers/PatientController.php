<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Patient;
class PatientController extends APIController
{
  function __construct(){
    $this->model = new PatientController();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); // $this->response['data']

    return $this->response();
  }
}
