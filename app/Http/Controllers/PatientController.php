<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Patient;
use App\VisitedPlace;
use Carbon\Carbon;
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
    foreach ($data as $key) {
      $data[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $data[$i]['places'] = VisitedPlace::where('account_id', '=', $key['account_id'])->get();
      $data[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[0]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A')
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
}
