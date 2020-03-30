<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RideHistory;
class RideHistoryController extends APIController
{
  function __construct(){
    $this->model = new RideHistory();
    $this->notRequired = array(
      'code'
    );
  }
}
