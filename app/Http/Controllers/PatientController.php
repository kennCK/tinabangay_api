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
      $places = VisitedPlace::where('account_id', '=', $key['account_id'])->get();
      $j = 0;
      foreach ($places as $placesKey) {
        $places[$j]['date_human'] = Carbon::createFromFormat('Y-m-d', $placesKey['date'])->copy()->tz($this->response['timezone'])->format('F j, Y');
        $places[$j]['time_human'] = Carbon::createFromFormat('Y-m-d H:i A', $placesKey['date'].' '.$placesKey['time'])->copy()->tz($this->response['timezone'])->format('H:i A');
        $j++;
      }
      $data[$i]['places'] = $places;
      $data[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }

  public function summary(Request $request){
    $this->response['data'] = array(
      'positive' => Patient::where('status', '=', 'positive')->count(),
      'pui'     => Patient::where('status', '=', 'pui')->count(),
      'pum'     => Patient::where('status', '=', 'pum')->count()
    );
    return $this->response();
  }
}
