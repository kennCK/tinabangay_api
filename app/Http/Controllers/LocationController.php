<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Location;
class LocationController extends APIController
{
  function __construct(){
    $this->model = new Location();
    $this->notRequired = array(
      'code'
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
    $result = Location::where($column, '=', $value)->where('code', '!=', null)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }
}
