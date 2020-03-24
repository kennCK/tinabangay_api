<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
class VisitedPlaceController extends APIController
{
  function __construct(){
    $this->model = new VisitedPlace();
  }

}
