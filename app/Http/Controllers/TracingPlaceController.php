<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use Illuminate\Support\Facades\DB;
class TracingPlaceController extends APIController
{
  public $existAccount = [];
  public $existPatient = [];
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
      ->join('patients AS T2', function($join){
          $join->on('T1.account_id', '=', 'T2.account_id')->whereNotNull('T1.account_id')
          ->orOn('T1.patient_id', '=', 'T2.id')->whereNotNull('T1.patient_id');
       })
      ->where('T2.status','=',$data['status'])
      ->whereNull('T2.deleted_at')
      ->whereNull('T1.deleted_at')
      ->select(['T1.*', 'T2.status'])
      ->get();
    $positiveUser = $positiveUser->groupBy(['route']);
    // $this->response['data'] = $positiveUser;
    // return $this->response();
    $array = array();
    foreach ($positiveUser as $key => $value) {
      $groupByAccount = $value->groupBy(['account_id', 'patient_id']);
      $positive = $this->getTotal($groupByAccount);
      // $this->response['data'] = $groupByAccount;
      // return $this->response();
      $place = VisitedPlace::where('id', '=', $value[0]->id)->first();
      $visitedPlaces = null;
      if(sizeof($this->existAccount) > 0 && sizeof($this->existPatient) > 0){
        $visitedPlaces = VisitedPlace::where('route', '=', $key)
        ->whereNotIn('account_id', $this->existAccount)
        ->orWhereNotIn('patient_id', $this->existPatient)
        ->get();
      }else if(sizeof($this->existAccount) > 0){
        $visitedPlaces = VisitedPlace::where('route', '=', $key)
        ->whereNotIn('account_id', $this->existAccount)
        ->get();
      }else if(sizeof($this->existPatient) > 0){
        $visitedPlaces = VisitedPlace::where('route', '=', $key)
        ->whereNotIn('patient_id', $this->existPatient)
        ->get();
      }
      
      $pui = 0;
      $pum = 0;
      $negative = 0;
      $death = 0;
      $recovered = 0;
      $visitedPlacesGroup =  $visitedPlaces->groupBy(['account_id', 'patient_id']);
      foreach ($visitedPlacesGroup as $keyVisitedPlaces => $valuesVisitedPlaces) {
        $patient = null;
        if(intval($keyVisitedPlaces) > 0){
          $patient = Patient::where('account_id', '=', $keyVisitedPlaces)->orderBy('created_at', 'desc')->first();
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
        }else{
          foreach ($valuesVisitedPlaces as $keyValue) {
            $patient = Patient::where('id', '=', $keyValue[0]->patient_id)->orderBy('created_at', 'desc')->first();
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
        }
      }
        
      $place['size'] = sizeof($visitedPlaces);
      $place['positive_size'] = $positive;
      $place['pui_size'] = $pui;
      $place['pum_size'] = $pum;
      $place['negative_size'] = $negative;
      $place['death_size'] = $death;
      $place['recovered_size'] = $recovered;
      // $place['visitedPlacesGroup'] = $visitedPlacesGroup;
      // $place['existing_account'] = $this->existAccount;
      // $place['existing_patient'] = $this->existPatient;
      $array[] = $place;
    }
    $keys = array_column($array, 'positive_size');
    array_multisort($keys, SORT_DESC, $array);
    $this->response['data'] = $array;
    return $this->response();
  }

  public function getTotal($result){
    $counter = 0;
    $this->existAccount = [];
    $this->existPatient = [];
    foreach ($result as $key => $value) {
      if(intval($key) > 0){
        $this->existAccount[] = $key;
        $counter++;
      }else{
        $counter += count($value);
        foreach ($value as $keyValue) {
          $this->existPatient[] = $keyValue[0]->patient_id;
        }
      }
    }
    return $counter;
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
    
    if (!empty($confirmed_places)) {
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
    }

    return 'negative';
  }
}
