<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Temperature;
use App\TemperatureLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class TemperatureController extends APIController
{
  function __construct(){
    $this->model = new Temperature();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    $i = 0;
    $data = $this->response['data'];
    foreach ($data as $key) {
      $data[$i]['added_by_account'] = $this->retrieveAccountDetails($key['added_by']);
      $location = TemperatureLocation::where('temperature_id', '=', $key['id'])->get();
      $data[$i]['temperature_location'] = sizeof($location) > 0 ? $location[0] : null;
      $data[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
  public function summary(Request $request){
    $data = $request->all();
    $temperatureLocation = DB::table('temperatures AS T1')
      ->join('temperature_locations AS T2','T1.id','=','T2.temperature_id')
      ->where('T2.locality','=',$data['locality'])
      ->where('T1.value','>=',$data['temperature'])
      ->select(['T1.*','T2.route','T2.locality','T2.country','T2.region'])
      ->get();
    $temperatureLocation = json_decode($temperatureLocation,true);
    $i = 0;
    foreach ($temperatureLocation as $key) {
      $temperatureLocation[$i]['added_by_account'] = $this->retrieveAccountDetails($key['added_by']);
      $temperatureLocation[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $temperatureLocation[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $i++;
    }
    $this->response['data'] = $temperatureLocation;
    return $this->response();
  }
}
