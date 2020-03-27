<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rides;
class RideController extends APIController
{
  function __construct(){
    $this->model = new Rides();
  }
}
