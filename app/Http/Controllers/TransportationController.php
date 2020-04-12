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

  public function getByParams($column, $value){
    $result = Transportation::where($column, '=', $value)->get();
    return (sizeof($result) > 0) ? $result[0] : null;
  }
}
