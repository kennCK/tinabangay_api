<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Patient;
use App\VisitedPlace;

class PatientController extends APIController
{
  function __construct(){
    $this->model = new Patient();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); 

    $i = 0;
    $data = $this->response['data'];
    foreach ($data as $key => $value) {
      $place[$i]['places'] = VisitedPlace::where('account_id', '=', $key['account_id'])->get();
      $i++;
    }

    return $this->response();
  }
}
