<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use Carbon\Carbon;
class VisitedPlaceController extends APIController
{
  public $tracingPlaceController = 'App\Http\Controllers\TracingPlaceController';
  public $patientController = 'App\Http\Controllers\PatientController';
  public $tracingController = 'App\Http\Controllers\TracingController';
  
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
      if($key['patient_id'] != null){
        // get status
        $this->response['data'][$i]['status'] = app($this->patientController)->getStatusByParams('id', intval($key['patient_id']));
      }else{
        $status = app($this->patientController)->getStatusByParams('account_id', intval($key['account_id']));
        if($status){
          $this->response['data'][$i]['status'] = $status;
        }else{
          $this->response['data'][$i]['status'] = app($this->tracingPlaceController)->getStatus($data[$i], floatval($radius)); 
        }
      }
      
      $this->response['data'][$i]['date_human'] = isset($key['date']) ? $this->daysDiffByDate($key['date']) : null;
      $this->response['data'][$i]['radius'] = $radius;
      $i++;
    }
    return $this->response();
  }
  
  public function retrieveTracing(Request $request){
    $condition = $request->all();

    $radius = env('RADIUS');
    if (!isset($radius)) {
      throw new \Exception('No env variable for "RADIUS"');
    }

    if (isset($condition['radius'])) {
      $radius = $condition['radius'];
    }

    $this->retrieveDB($condition); // store to 
    $data = $this->response['data'];
    $i = 0;
    $result = array();
    
    foreach ($data as $key) {
      if($key['patient_id'] != null){
        // get status
        $patient = app($this->patientController)->getStatusByParams('id', intval($key['patient_id']));
        $this->response['data'][$i]['status'] = $patient ? $patient['status'] : null;
        $this->response['data'][$i]['status_label'] = $patient ? $patient['status'] : null;
        $this->response['data'][$i]['remarks'] = $patient ? $patient['remarks'] : null;;
      }else{
        $patient = app($this->patientController)->getStatusByParams('account_id', intval($key['account_id']));
        if($patient){
          $this->response['data'][$i]['status'] = $patient ? $patient['status'] : null;
          $this->response['data'][$i]['status_label'] = $patient ? $patient['status'] : null;
          $this->response['data'][$i]['remarks'] = $patient ? $patient['remarks'] : null;
        }else{
          $status = app($this->tracingPlaceController)->getStatus($data[$i], floatval($radius));
          $this->response['data'][$i]['status'] = $status;
          $this->response['data'][$i]['status_label'] = $status != 'negative' ? 'EXPOSED WITH '.$status.' THE LAST '.env('SPECIFIED_DAYS').'DAYS' : 'CLEAR THE LAST '.env('SPECIFIED_DAYS').' DAYS';
          $this->response['data'][$i]['remarks'] = null;
        }
      }
      if($key['account_id'] != null){
        $this->response['data'][$i]['account'] = $this->retrieveAccountDetailsOnlyImportant($key['account_id']);
      }else{
        $this->response['data'][$i]['account'] = null;
      }
      
      $this->response['data'][$i]['date_human'] = isset($key['date']) ? $this->daysDiffByDate($key['date']) : null;
      $this->response['data'][$i]['radius'] = $radius;
      $i++;
    }
    return $this->response();
  }

  public function retrieveCustomers(Request $request){
    $condition = $request->all();

    $radius = env('RADIUS');
    if (!isset($radius)) {
      throw new \Exception('No env variable for "RADIUS"');
    }

    if (isset($condition['radius'])) {
      $radius = $condition['radius'];
    }

    $this->retrieveDB($condition); // store to 
    $data = $this->response['data'];
    $i = 0;
    $result = array();
    
    foreach ($data as $key) {
      if($data[$i]['account_id'] !== null){
        $status = app($this->tracingController)->getStatusByAccountId($data[$i]['account_id']);
        $data[$i]['status'] =  $status['status'];
        $data[$i]['status_from'] =  $status['status_from'];
        $data[$i]['status_label'] =  $status['status_label'];
        $data[$i]['account'] = $this->retrieveAccountDetailsOnlyImportant($data[$i]['account_id']);
        $data[$i]['date_human'] = isset($data[$i]['date']) ? $this->daysDiffByDate($data[$i]['date']) : null;
        $data[$i]['created_at_human'] = $this->daysDiffDateTime($data[$i]['created_at']);
        $result[] = $data[$i];
      }
      $i++;
    }
    if(sizeof($condition['condition']) == 4){
      $con = $condition['condition'];
        $this->response['size'] = VisitedPlace::where($con[0]['column'], $con[0]['clause'], $con[0]['value'])
        ->where($con[2]['column'], $con[2]['clause'], $con[2]['value'])
        ->where($con[3]['column'], $con[3]['clause'], $con[3]['value'])
        ->count();
    }
    $this->response['data'] = $result;
    return json_encode(array(
      'data'  => $result,
      'size'  => $this->response['size'],
      'timezone'  => $this->response['timezone'],
      'request_timestamp' => $this->response['request_timestamp'],
      'debug' => $this->response['debug'],
      'error' => $this->response['error']
    ));
  }

  public function retrieveCustomersLimited(Request $request){
    $condition = $request->all();
    $con = $condition['condition'];

    $data = VisitedPlace::select('id', 'date', 'created_at', 'account_id')->where($con[0]['column'], $con[0]['clause'], $con[0]['value'])
        ->where($con[1]['column'], $con[1]['clause'], $con[1]['value'])
        ->where($con[2]['column'], $con[2]['clause'], $con[2]['value'])
        ->where($con[3]['column'], $con[3]['clause'], $con[3]['value'])
        ->limit($condition['limit'])
        ->offset($condition['offset'])
        ->get();
    $i = 0;
    $result = array();

    foreach ($data as $key) {
      if($data[$i]['account_id'] !== null){
        $status = app($this->tracingController)->getStatusByAccountId($data[$i]['account_id']);
        $data[$i]['status'] =  $status['status'];
        $data[$i]['status_from'] =  $status['status_from'];
        $data[$i]['status_label'] =  $status['status_label'];
        $data[$i]['account'] = $this->retrieveAccountDetailsOnlyImportant($data[$i]['account_id']);
        $data[$i]['date_human'] = isset($data[$i]['date']) ? $this->daysDiffByDate($data[$i]['date']) : null;
        $data[$i]['created_at_human'] = $this->daysDiffDateTime($data[$i]['created_at']);
        $result[] = $data[$i];
      }
      $i++;
    }
    if(sizeof($condition['condition']) == 4){
        $this->response['size'] = VisitedPlace::where($con[0]['column'], $con[0]['clause'], $con[0]['value'])
        ->where($con[2]['column'], $con[2]['clause'], $con[2]['value'])
        ->where($con[3]['column'], $con[3]['clause'], $con[3]['value'])
        ->count();
    }
    
    $this->response['data'] = $result;
    return json_encode(array(
      'data'  => $result,
      'size'  => $this->response['size'],
      'timezone'  => $this->response['timezone'],
      'request_timestamp' => $this->response['request_timestamp'],
      'debug' => $this->response['debug'],
      'error' => $this->response['error']
    ));
  }

  public function getByParams($column, $value){
    $places = VisitedPlace::where($column, '=', $value)->get();
    $j = 0;
    foreach ($places as $key) {
      $places[$j]['date_human'] = isset($key['date']) ? $this->daysDiffByDate($key['date'], "Y-m-d") : null;
        $j++;
    }
    return $places;
  }
}
