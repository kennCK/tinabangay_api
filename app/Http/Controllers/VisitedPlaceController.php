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
      $this->response['data'][$i]['status'] = app($this->tracingPlaceController)->getStatus($data[$i], $radius);
      $this->response['data'][$i]['date_human'] = $this->daysDiff($key['date']);
      $this->response['data'][$i]['radius'] = $radius;
      $i++;
    }
    return $this->response();
  }

  public function getByParams($column, $value){
    $places = VisitedPlace::where($column, '=', $value)->get();
    $j = 0;
    foreach ($places as $key) {
      $places[$j]['date_human'] = $this->daysDiff($key['date']);
        $j++;
    }
    return $places;
  }


}
