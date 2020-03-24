<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QrCode;
class QrCodeController extends APIController
{
  function __construct(){
    $this->model = new QrCode();
  }

  public function generate(Request $request){
    //
  }
}
