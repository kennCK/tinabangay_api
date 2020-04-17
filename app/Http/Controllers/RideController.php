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
      $datenow = Carbon::now()->format("Y-m-d H:i:s");
      $end_date = Carbon::parse(Carbon::createFromFormat('Y-m-d', $key['created_at'])->format("Y-m-d H:i:s"));
      $days = $end_date->diffInDays($datenow);
      // Carbon::createFromFormat('Y-m-d H:i:s', $key['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A')
      $data[$i]['created_at_human'] = "$days days";
      if($key['payload'] == 'manual'){
        $data[$i]['transportation'] = null;
        $fromTo = $this->checkRoute($key);
        $data[$i]['from_status'] = $fromTo['from'];
        $data[$i]['to_status'] = $fromTo['to']; // work on this later
        $data[$i]['from_date_human'] = Carbon::createFromFormat('Y-m-d H:i:s',  $data[$i]['from_date_time'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
        $data[$i]['to_date_human'] = Carbon::createFromFormat('Y-m-d H:i:s',  $data[$i]['to_date_time'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      }else if($key['payload'] == 'qr'){
        $data[$i]['transportation'] = app($this->transportationClass)->getByParams('account_id', $key['owner']);
         $data[$i]['from_status'] = 'negative';// work on this later
        $data[$i]['to_status'] = 'negative'; // work on this later
      }
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
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
