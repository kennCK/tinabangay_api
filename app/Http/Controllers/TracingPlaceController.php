<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
class TracingPlaceController extends APIController
{
  public function places(){
    $positiveUser = Patient::where('status', '=', 'positive')->get();
    if(sizeof($positiveUser) > 0){
      $i = 0;
      foreach ($positiveUser as $key) {
        $positiveUser[$i]['places'] = VisitedPlace::where('deleted_at',  '=', null)->get();
        $i++;
      }
    }
    $this->response['data'] = $positiveUser;
    return $this->response();
  }
}
