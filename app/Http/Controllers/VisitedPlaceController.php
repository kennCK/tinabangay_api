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
    foreach ($data as $key => $value) {
      $this->response['data'][$i]['status'] = app($this->tracingPlaceController)->getStatus($data[$i]);
      $i++;
    }
    return $this->response();
  }

  public function getByParams($column, $value){
    $places = VisitedPlace::where($column, '=', $value)->get();
    $j = 0;
    foreach ($places as $key) {
      $places[$j]['date_human'] = Carbon::createFromFormat('Y-m-d', $placesKey['date'])->copy()->tz($this->response['timezone'])->format('F j, Y');
        $j++;
    }
    return $places;
  }


}
