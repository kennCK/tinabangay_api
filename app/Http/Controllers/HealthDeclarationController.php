<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HealthDeclaration;
class HealthDeclarationController extends APIController
{
  function __construct(){
    $this->model = new HealthDeclaration();
  }

  public function create(Request $request){
    $data = $request->all();
    $data['code'] = $this->generateCode();
    $this->model = new HealthDeclaration();
    $this->insertDB($data);
    return $this->response();
  }

  public function generateCode(){
    $code = 'HDF-'.substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 60);
    $codeExist = HealthDeclaration::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }
}
