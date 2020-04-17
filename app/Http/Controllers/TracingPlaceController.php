<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use Illuminate\Support\Facades\DB;
class TracingPlaceController extends APIController
{
  public function places(Request $request){
    $isPaginate = false;
    $page_start = 0;
    $page_end = 10;
    if (isset($request->offset) && isset($request->limit)) {
      $isPaginate = true;
      $page_start =  $request->offset == 1 ? 0 :  $request->limit*(($request->offset)); 
      $page_end = $page_start+$request->limit;
    }
    $data = $request->all();
    $positiveUser = DB::table('visited_places AS T1')
      ->join('patients AS T2','T2.account_id','=','T1.account_id')
      ->where('T2.status','=',$data['status'])
      ->whereNull('T2.deleted_at')
      ->whereNull('T1.deleted_at')
      ->select('T1.*')
      ->get();
    $positiveUser = $positiveUser->groupBy('route');
    $array = array();
    foreach ($positiveUser as $key => $value) {
      $groupByAccount = $value->groupBy('account_id');
      $place = VisitedPlace::where('route', '=', $key)->first();
      $visitedPlaces = VisitedPlace::where('route', '=', $key)->where('account_id', '!=',  $positiveUser->id)->get();
      $pui = 0;
      $pum = 0;
      $positive = count($groupByAccount);
      $negative = 0;
      $death = 0;
      $recovered = 0;
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
            case 'negative':
              $negative++;
              break; 
            case 'recovered':
              $recovered++;
            case 'death':
              $death++;
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
        $place['death_size'] = $death;
        $place['recovered_size'] = $recovered;
        $array[] = $place;
      }
      // $keys = array_column($array, 'positive_size');
      // array_multisort($keys, SORT_DESC, $array);
      // $this->response['data'] = $array;
      // return $this->response();
      $keys = array_column($array, 'positive_size');
      array_multisort($keys, SORT_DESC, $array);
     //  if ($isPaginate ==  true) {
     //  $start = ($page_start > count($array)) ? 0 : $page_start ;
     //  $end = ($page_end > count($array)) ? count($array) : $page_end ;
     //   $paged_array = array();
     //  for ($i = $start; $i < $end ; $i++) { 
     //     array_push($paged_array, $array[$i]);
     //  }
     //  $array = $paged_array; 
     // }
     $this->response['data'] = $array;
     return $this->response();
  }

  public function getDistance($lat1, $lon1, $lat2, $lon2) {  
    $earth_radius_in_km = 6371;
  
    $rad_lat = deg2rad($lat2 - $lat1);  
    $rad_lon = deg2rad($lon2 - $lon1);  
  
    $a = sin($rad_lat/2) * sin($rad_lat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($rad_lon/2) * sin($rad_lon/2);  
    $c = 2 * asin(sqrt($a));  
    $distance = $earth_radius_in_km * $c;  
  
    return $distance;  
  }

  public function getStatus($location, $radius){
    $lat = $location['latitude'];
    $lon = $location['longitude'];
    
    $confirmed_places = DB::table('visited_places AS T1')
      ->join('patients AS T2','T2.account_id','=','T1.account_id')
      ->whereNull('T2.deleted_at')
      ->whereNull('T1.deleted_at')
      ->select(['T1.*', 'T2.status'])->get();
    $confirmed_places = json_decode($confirmed_places, true);

    $i = 0;
    foreach ($confirmed_places as $coord) {
      $distance = TracingPlaceController::getDistance($lat, $lon, $coord['latitude'], $coord['longitude']);
      if ($distance < $radius) {
        $all_status[$i] = $coord['status'];
      } else {
        $all_status[$i] = 'negative';
      }
      $i++;
    }

    if (in_array('positive', $all_status)) {
      return 'positive';
    }
    if (in_array('pui', $all_status)) {
      return 'pui';
    }
    if (in_array('pum', $all_status)) {
      return 'pum';
    }
    if (in_array('death', $all_status)) {
      return 'death';
    }
    if (in_array('recovered', $all_status)) {
      return 'recovered';
    }
    if (in_array('negative', $all_status)) {
      return 'negative';
    }

    return 'negative';
  }
}
