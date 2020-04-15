<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use Carbon\Carbon;
class VisitedPlaceController extends APIController
{
  public $tracingPlaceController = 'App\Http\Controllers\TracingPlaceController';
  
  function __construct(){
    $this->model = new VisitedPlace();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); // store to 
    $data = $this->response['data'];
    $i = 0;
    
    foreach ($data as $key) {
      // $db_date = Carbon::createFromFormat('Y-m-d', $key['date'])->copy()->tz($this->response['timezone'])->format('Y-m-d');
      // $curr_date = Carbon::now()->copy()->tz($this->response['timezone'])->format('Y-m-d');
      $end_date = Carbon::parse(Carbon::now()->format("Y-m-d H:i:s"));
      $start_date = Carbon::Parse(Carbon::createFromFormat('Y-m-d', $key['date'])->format("Y-m-d H:i:s"));
      $days = $start_date->diffInDays($end_date);
      $hour = $start_date->copy()->addDays($days)->diffInHours($end_date);
      $minute = $end_date->copy()->addDays($days)->addHours($hour)->diffInMinutes($end_date);
      $dayRes = $days!=0?$days:'';
      $hourRes = $hour!=0?$hour:$hour;
      $minRes =  $minute!=0?$minute:'';
      $this->response['data'][$i]['status'] = app($this->tracingPlaceController)->getStatus($data[$i]);
      $this->response['data'][$i]['date_human'] = "$dayRes days, $hourRes h:$minRes min";
      $i++;
    }
    return $this->response();
  }

  public function getByParams($column, $value){
    $places = VisitedPlace::where($column, '=', $value)->get();
    $j = 0;
    foreach ($places as $key) {
      $end_date = Carbon::parse(Carbon::now()->format("Y-m-d H:i:s"));
      $start_date = Carbon::Parse(Carbon::createFromFormat('Y-m-d', $key['date'])->format("Y-m-d H:i:s"));
      $days = $start_date->diffInDays($end_date);
      $hour = $start_date->copy()->addDays($days)->diffInHours($end_date);
      $minute = $end_date->copy()->addDays($days)->addHours($hour)->diffInMinutes($end_date);
      $dayRes = $days!=0?$days:'';
      $hourRes = $hour!=0?$hour:$hour;
      $minRes =  $minute!=0?$minute:'';
      $places[$j]['date_human'] = "$dayRes days, $hourRes h:$minRes min";;
        $j++;
    }
    return $places;
  }


}
