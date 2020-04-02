<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Location;
class LocationController extends APIController
{
  function __construct(){
    $this->model = new Location();
  }
}
