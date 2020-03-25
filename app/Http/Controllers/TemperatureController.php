<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Temperature;
use App\TemperatureLocation;
use Carbon\Carbon;
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
}
