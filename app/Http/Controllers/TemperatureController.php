<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Temperature;
class TemperatureController extends APIController
{
  function __construct(){
    $this->model = new Temperature();
  }
}
