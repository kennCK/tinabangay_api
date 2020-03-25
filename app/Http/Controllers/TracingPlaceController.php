<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use Illuminate\Support\Facades\DB;
class TracingPlaceController extends APIController
{
  public function places(){
    $positiveUser = DB::table('visited_places AS T1')
                      ->join('patients AS T2','T2.account_id','=','T1.account_id')
                      ->where('T2.status','=','positive')
                      ->whereColumn('T2.updated_at','<=','T1.created_at')
                      ->whereNull('T2.deleted_at')
                      ->whereNull('T1.deleted_at')
                      ->select('T1.*')->get();
    
    $this->response['data'] = $positiveUser->groupBy('locality');
    return $this->response();
  }
}
