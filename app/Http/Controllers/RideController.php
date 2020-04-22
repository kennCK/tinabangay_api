<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Patient;
use App\VisitedPlace;
use App\Ride;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class RideController extends APIController
{

  public $transportationClass = 'App\Http\Controllers\TransportationController';
  function __construct(){
    $this->model = new Ride();
    $this->notRequired = array(
      'owner',
      'transportation_id',
      'from',
      'from_date_time',
      'to',
      'to_date_time',
      'type',
      'code'
    );
  }
  
  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    $i = 0;
    $data = $this->response['data'];
    foreach ($data as $key) {
      $data[$i]['created_at_human'] = $this->daysDiffDateTime($data[$i]['created_at']);
      if($key['payload'] == 'manual'){
        $data[$i]['transportation'] = null;
        $route_status = $this->checkRoute($key);
        $data[$i]['from_status'] = $route_status['from'];
        $data[$i]['to_status'] = $route_status['to'];
        $data[$i]['from_date_human'] = $this->daysDiffDateTime($data[$i]['from_date_time']);
        $data[$i]['to_date_human'] = $this->daysDiffDateTime($data[$i]['to_date_time']);
      }else if($key['payload'] == 'qr'){
        $data[$i]['transportation'] = app($this->transportationClass)->getByParams('account_id', $key['owner']);
        $route_status = $this->checkQrRoute($key);
        $data[$i]['from_status'] = $route_status;
        $data[$i]['to_status'] = $route_status;
      }
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
  public function checkQrRoute($route){
    $retVal = 'negative';
    $possibleStatus = array('death','positive','pum','pui','negative');
    $rides_status = DB::table('rides AS T1')
      ->join("patients AS T2","T1.account_id",'=','T2.account_id')
      ->whereIn('T1.transportation_id', [$route['transportation_id']])
      ->select(['T2.status AS status'])
      ->get();
    $rides_status = json_decode($rides_status,true);
    foreach ($rides_status as $key => $value) {
      if (array_search($value['status'], $possibleStatus) < array_search($retVal, $possibleStatus)){
        $retVal = $value['status'];
      }
    }
    return $retVal;
  }
  public function checkRoute($route){
    $retVal = array('from'=>'negative','to'=>'negative');
    $possibleStatus = array('death','positive','pum','pui','negative');
    $routes = DB::table('visited_places AS T1')
      ->join("patients AS T2","T1.account_id",'=','T2.account_id')
      ->whereIn('T1.route',[$route['from'],$route['to']])
      ->select(['T1.route AS route','T2.status AS status'])
      ->get();
    $routes = json_decode($routes,true); 
    foreach ($routes as $key => $value) {
      if ($value['route']==$route['from']){
        if (array_search($value['status'],$possibleStatus)<array_search($retVal['from'],$possibleStatus)){
          $retVal['from'] = $value['status'];
        }
      }else if ($value['route']==$route['to']){
        if (array_search($value['status'],$possibleStatus)<array_search($retVal['to'],$possibleStatus)){
          $retVal['to'] = $value['status'];
        }
      }
    }
    return $retVal;
  }
}
