<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Symptom;
class SymptomController extends APIController
{
  function __construct(){
    $this->model = new Symptom();
    $this->notRequired = array(
      'remarks'
    );
  }
}
