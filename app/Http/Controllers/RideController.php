<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ride;
use Carbon\Carbon;
class RideController extends APIController
{
  function __construct(){
    $this->model = new Ride();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    $i = 0;
    $data = $this->response['data'];
    foreach ($data as $key) {
      $data[$i]['status'] = 'negative'; // work on this later
      $data[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $data[$i]['from_date_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['from_date_time'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $data[$i]['to_date_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['to_date_time'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
}
