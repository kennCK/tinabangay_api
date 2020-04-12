<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transportation;
use Carbon\Carbon;
class TransportationController extends APIController
{
  function __construct(){
    $this->model = new Transportation();
    $this->notRequired = array(
      'number'
    );
  }

  public function update(Request $request){
    $data = $request->all();
    Transportation::where('id', '=', $data['id'])->update(
      array(
        'updated_at' => Carbon::now()
      )
    );
    $this->response['data'] = true;
    return $this->response();
  }

  public function retrieveNotifications(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    $result = $this->response['data'];
    if(sizeof($result) > 0){
      $i = 0;
      foreach ($result as $key) {
        $result[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
        $i++;
      }
    }
    $this->response['data'] = $result;
    return $this->response();
  }

  public function getByParams($column, $value){
    $result = Transportation::where($column, '=', $value)->orderBy('updated_at', 'desc')->get();
    return (sizeof($result) > 0) ? $result[0] : null;
  }
}
