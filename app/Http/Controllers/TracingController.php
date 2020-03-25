<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use App\UserInformation;

class TracingController extends Controller
{
    public function tree(){
        $placesList = Patient::with('userinfo')->get();
        if(!empty($placesList)){
          $jsonData = json_encode($placesList->toArray());
          dd($jsonData);
        }
     

    }
}
