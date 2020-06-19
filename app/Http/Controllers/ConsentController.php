<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Consent;
class ConsentController extends APIController
{
  function __construct(){
    $this->model = new Consent();
  }
}
