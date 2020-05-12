<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Complaint;
class ComplaintController extends APIController
{
  function __construct(){
    $this->model = new Complaint();
  }
}
