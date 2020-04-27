<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use Carbon\Carbon;
class VisitedPlaceController extends APIController
{
  public $tracingPlaceController = 'App\Http\Controllers\TracingPlaceController';
  public $patientController = 'App\Http\Controllers\PatientController';
  
  function __construct(){
    $this->model = new VisitedPlace();
    $this->notRequired = array(
      'account_id',
      'patient_id',
      'date',
      'time'
    );
  }

  public function retrieve(Request $request){
    $data = $request->all();

    $radius = env('RADIUS');
    if (!isset($radius)) {
      throw new \Exception('No env variable for "RADIUS"');
    }

    if (isset($data['radius'])) {
      $radius = $data['radius'];
    }

    $this->retrieveDB($data); // store to 
    $data = $this->response['data'];
    $i = 0;
    
    foreach ($data as $key) {
      if($key['patiend_id'] != null){
        // get status
        $this->response['data'][$i]['status'] = app($this->patientController)->getStatusByParams('id', intval($key['patiend_id']));
      }else{
        $status = app($this->patientController)->getStatusByParams('id', intval($key['account_id']));
        if($status){
          $this->response['data'][$i]['status'] = $status;
        }else{
          $this->response['data'][$i]['status'] = app($this->tracingPlaceController)->getStatus($data[$i], $radius); 
        }
      }
      
      $this->response['data'][$i]['date_human'] = isset($key['date']) ? $this->daysDiffByDate($key['date']) : null;
      $this->response['data'][$i]['radius'] = $radius;
      $i++;
    }
    return $this->response();
  }

  public function getByParams($column, $value){
    $places = VisitedPlace::where($column, '=', $value)->get();
    $j = 0;
    foreach ($places as $key) {
      $places[$j]['date_human'] = isset($key['date']) ? $this->daysDiffByDate($key['date']) : null;
        $j++;
    }
    return $places;
  }
}
