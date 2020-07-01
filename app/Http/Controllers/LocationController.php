<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Location;
class LocationController extends APIController
{
  function __construct(){
    $this->model = new Location();
    $this->notRequired = array(
      'code', 'assigned_code', 'payload', 'longitude', 'latitude', 'route', 'locality', 'country', 'region'
    );
  }

  public function create(Request $request){
    $data = $request->all();
    if(isset($data['autogenerate'])){
      $data['code'] = $this->generateCode();
    }
    $this->model = new Location();
    $this->insertDB($data);
    return $this->response();
  }

  public function generateCode(){
    $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 32);
    $codeExist = Location::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }

  public function getByParamsWithCode($column, $value){
    $result = Location::where($column, '=', $value)->where('code', '!=', null)->where('payload', '=', 'brgy')->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }

  public function getAssignedLocation($column, $value){
    $result = Location::where($column, '=', $value)->where('assigned_code', '!=', null)->where('payload', '=', 'business')->get();
    if(sizeof($result) > 0){
      $newResult = Location::where('code', '=', $result[0]['assigned_code'])->get();
      if(sizeof($newResult) > 0){
        $newResult[0]['id'] = $result[0]['id'];
      }
      return sizeof($newResult) > 0 ? $newResult[0] : null;
    }
    return null;
  }
}
