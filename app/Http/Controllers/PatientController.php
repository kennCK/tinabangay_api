<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Patient;
class PatientController extends Controller
{
  function __construct(){
    $this->model = new PatientController();
  }
}
