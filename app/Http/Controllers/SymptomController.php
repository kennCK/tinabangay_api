<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Symptom;
class SymptomController extends APIController
{
  function __construct(){
    $this->model = new Symptom();
    $this->notRequired = array(
      'remarks'
    );
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); 
    $i = 0;
    $data = $this->response['data'];
    foreach ($data as $key) {
      $data[$i]['date_human'] = $this->daysDiffByDate($key['date']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
}
