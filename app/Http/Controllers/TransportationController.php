<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transportation;
class TransportationController extends APIController
{
  function __construct(){
    $this->model = new Transportation();
  }
}
