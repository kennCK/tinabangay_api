<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BrgyCode;
class BrgyCodeController extends APIController
{
  function __construct(){
    $this->model = new BrgyCode();
  }

  function retrieve(Request $request){
    $data = $request->all();
    $this->response['data'] = 'retrieve brgy codes';
    return $this->response(); 
  }
}
