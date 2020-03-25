<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use Illuminate\Support\Facades\DB;
class TracingPlaceController extends APIController
{
  public function places(Request $request){
    $data = $request->all();
    $positiveUser = DB::table('visited_places AS T1')
      ->join('patients AS T2','T2.account_id','=','T1.account_id')
      ->where('T2.status','=',$data['status'])
      ->whereNull('T2.deleted_at')
      ->whereNull('T1.deleted_at')
      ->select('T1.*')->get();
    
    $positiveUser = $positiveUser->groupBy('route');
    $array = array();
    foreach ($positiveUser as $key => $value) {
      $place = VisitedPlace::where('route', '=', $key)->first();
      $visitedPlaces = VisitedPlace::where('route', '=', $key)->get();
      $pui = 0;
      $pum = 0;
      $positive = 0;
      $negative = 0;
      foreach ($visitedPlaces as $keyVisitedPlaces) {
        $patient = Patient::where('account_id', '=', $keyVisitedPlaces->account_id)->orderBy('created_at', 'desc')->first();
        if($patient){
          switch ($patient->status) {
            case 'pui':
              $pui++;
              break;
            case 'pum':
              $pum++;
              break;
            case 'positive':
             $positive++;
             break; 
          }
        }else{
          $negative++;
        }
      }
      $place['size'] = sizeof($visitedPlaces);
      $place['positive_size'] = $positive;
      $place['pui_size'] = $pui;
      $place['pum_size'] = $pum;
      $place['negative_size'] = $negative;
      $array[] = $place;
    }
    $keys = array_column($array, 'positive_size');
    array_multisort($keys, SORT_DESC, $array);
    $this->response['data'] = $array;
    return $this->response();
  }
}