<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rides;

class RidesController extends Controller
{
    function __construct(){
        $this->model = new Rides();
    }
}
