<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Patient;
class PatientController extends APIController
{
  function __construct(){
    $this->model = new PatientController();
  }
}
