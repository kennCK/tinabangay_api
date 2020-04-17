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
      // $curr_date = Carbon::now()->copy()->tz($this->response['timezone'])->format('Y-m-d')
      $this->response['data'][$i]['status'] = app($this->tracingPlaceController)->getStatus($data[$i]);
      $this->response['data'][$i]['date_human'] = Carbon::createFromFormat('Y-m-d', $key['date'])->copy()->tz($this->response['timezone'])->format('Y-m-d');
      $i++;
    }
    return $this->response();
  }

  public function getByParams($column, $value){
    $places = VisitedPlace::where($column, '=', $value)->get();
    $j = 0;
    foreach ($places as $key) {
      $places[$j]['date_human'] = Carbon::createFromFormat('Y-m-d', $key['date'])->copy()->tz($this->response['timezone'])->format('F j, Y');
        $j++;
    }
    return $places;
  }
}
