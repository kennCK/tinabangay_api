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
        'date'        =>  null,
        'time'        =>  null
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
      $end_date = Carbon::parse(Carbon::now()->format("Y-m-d H:i:s"));
      $start_date = Carbon::Parse(Carbon::createFromFormat('Y-m-d', $key['created_at'])->format("Y-m-d H:i:s"));
      $days = $start_date->diffInDays($end_date);
      $hour = $start_date->copy()->addDays($days)->diffInHours($end_date);
      $minute = $end_date->copy()->addDays($days)->addHours($hour)->diffInMinutes($end_date);
      $dayRes = $days!=0?$days:'';
      $hourRes = $hour!=0?$hour:$hour;
      $minRes =  $minute!=0?$minute:'';
      $temperatureLocation[$i]['added_by_account'] = $this->retrieveAccountDetails($key['added_by']);
      $temperatureLocation[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $temperatureLocation[$i]['created_at_human'] = "$dayRes days, $hourRes h:$minRes min";;
      $i++;
    }
    $this->response['data'] = $temperatureLocation;
    return $this->response();
  }
}
