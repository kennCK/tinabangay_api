<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Temperature;
use App\TemperatureLocation;
use App\VisitedPlace;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class TemperatureController extends APIController
{
  function __construct(){
    $this->model = new Temperature();
    $this->notRequired = array(
      'remarks'
    );
  }

  public function create(Request $request){
    $data = $request->all();
    $this->insertDB($data);
    if($data['location'] != null){
      $visitedPlaces = array(
        'created_at'  => Carbon::now(),
        'account_id'  => $data['account_id'],
        'route'       => $data['location']['route'],
        'region'      => $data['location']['region'],
        'locality'    => $data['location']['locality'],
        'country'     => $data['location']['country'],
        'longitude'    => $data['location']['longitude'],
        'latitude'     => $data['location']['latitude'],
        'date'        =>  Carbon::parse(Carbon::now()->format("Y-m-d")),
        'time'        =>  Carbon::parse(Carbon::now()->format("H:i:s"))
      );
      VisitedPlace::insert($visitedPlaces);
    }
    return $this->response();
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
      $data[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
  
  public function retrieveTracing(Request $request){
    if($this->checkAuthenticatedUser(true) == false){
      return $this->response();
    }
    $data = $request->all();
    $temperatures = DB::table('temperatures AS T1')
      ->join('locations AS T2', 'T2.account_id', '=', 'T1.account_id')
      ->where('T2.locality', 'like', $data['locality'])
      ->where('T2.region', 'like', $data['region'])
      ->where('T2.country', 'like', $data['country'])
      ->where('T1.value','>=', $data['temperature'])
      ->whereNull('T2.deleted_at')
      ->whereNull('T1.deleted_at')
      ->orderBy('T1.'.$data['sort']['column'], $data['sort']['value'])
      ->select(['T1.*','T2.route','T2.locality','T2.country','T2.region'])
      ->get();
    $results = json_decode($temperatures, true);
    $i = 0;
    foreach ($results as $key) {
      $results[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $results[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $i++;
    }
    $this->response['data'] = $results;
    return $this->response();
  }

  public function summary(Request $request){
    if($this->checkAuthenticatedUser(true) == false){
      return $this->response();
    }
    $data = $request->all();
    $temperatureLocation = DB::table('temperatures AS T1')
      ->join('temperature_locations AS T2','T1.id','=','T2.temperature_id')
      ->where('T2.locality','=',$data['locality'])
      ->where('T1.value','>=',$data['temperature'])
      ->select(['T1.*','T2.route','T2.locality','T2.country','T2.region'])
      ->get();
    $temperatureLocation = json_decode($temperatureLocation, true);
    $i = 0;
    foreach ($temperatureLocation as $key) {
      $temperatureLocation[$i]['added_by_account'] = $this->retrieveAccountDetails($key['added_by']);
      $temperatureLocation[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $temperatureLocation[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $i++;
    }
    $this->response['data'] = $temperatureLocation;
    return $this->response();
  }
}
