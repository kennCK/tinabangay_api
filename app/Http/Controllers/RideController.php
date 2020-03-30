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
      $fromTo = $this->checkRoute($key);
      $data[$i]['from_status'] = $fromTo['from'];
      $data[$i]['to_status'] = $fromTo['to']; // work on this later
      $data[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $data[$i]['from_date_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['from_date_time'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $data[$i]['to_date_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['to_date_time'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
  public function checkRoute($route){
    $routes[0] = DB::table('visited_places AS T1')
      ->join("patients AS T2","T1.account_id",'=','T2.account_id')
      ->where('T1.route','=',$route['from'])
      ->where('T1.account_id','!=',$route['account_id'])
      ->pluck('status');
    $routes[1] = DB::table('visited_places AS T1')
      ->join("patients AS T2","T1.account_id",'=','T2.account_id')
      ->where('T1.route','=',$route['to'])
      ->where('T1.account_id','!=',$route['account_id'])
      ->pluck('status');
    $person = Patient::where("account_id","=",$route['account_id'])
      ->whereNull("deleted_at")
      ->pluck('status');
    $person = json_decode($person,true);
    for ($i=0 ; $i<2 ; $i++){
      if (count($routes[$i])==0){
        if (isset($person[0])){
          $routes[$i] = $person[0];
        }else{
          $routes[$i] = 'negative';
        }
      }else {
        $routes[$i] = $routes[$i]->groupBy('status');
        $routes[$i] = json_decode($routes[$i],true); 
        $routes[$i] = $routes[$i][null];
        if (in_array('death',$routes[$i]) || (isset($person[0]) && $person[0]=='death')){
          $routes[$i] = 'death';
        }else if (in_array('positive',$routes[$i]) || (isset($person[0]) && $person[0]=='positive')){
          $routes[$i] = 'positive';
        }else if (in_array('pum',$routes[$i]) || (isset($person[0]) && $person[0]=='pum')){
          $routes[$i] = 'pum';
        }else if (in_array('pui',$routes[$i]) || (isset($person[0]) && $person[0]=='pui')){
          $routes[$i] = 'pui';
        }else {
          $routes[$i] = 'negative';
        }
      }
    }
    $routes['from'] = &$routes[0];
    unset($routes[0]);
    $routes['to'] = &$routes[1];
    unset($routes[1]);
    return $routes;
  }
}
