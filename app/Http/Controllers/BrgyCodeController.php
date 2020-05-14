<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BrgyCode;
class BrgyCodeController extends APIController
{
  function __construct(){
    $this->model = new BrgyCode();
    $this->notRequired = array('image_id');
  }
}
