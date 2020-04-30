<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use App\UserInformation;

use App\Account;
use App\Temperature;
use App\Ride;

use DB;
use Carbon\Carbon;

class TracingController extends APIController
{
    public $tracingPlaceController = 'App\Http\Controllers\TracingPlaceController';
    public $rideController = 'App\Http\Controllers\RideController';

    public function tree(){
        $placesList = VisitedPlace::with('patients','userInfo')->get();
        $this->retrieveDB($placesList);
        return $this->response();
        
//         if(!empty($placesList)){
//           $jsonData = json_encode($placesList->toArray());
//           dd($jsonData);
//         }
        
        // $list = DB::table('visited_places')
        //         ->join('patients', 'patients.account_id', '=', 'visited_places.account_id')
        //         ->join('account_informations', 'account_informations.account_id', '=', 'patients.account_id')
        //         ->select('*')
        //         ->get();
        
        // if(!empty($list)){
        //     $jsonData = json_encode($list->toArray());
        //     dd($jsonData);
        // }
     
    }

    public function getHistory($username, $agent_id){
      // retrieve the history of visited places, temperature 
      // and transportation 
      // of the user by passing 
      // the username and id of the agent

      $agentExists = Account::where('id','=', $agent_id)->where('account_type','=', 'AGENCY')->get();
      
      $this->response['data'] = null;
      if(sizeof($agentExists) > 0){
        $userExists = Account::where('id', '=', $username)->where('account_type', '=','USER')->get();
        if(sizeof($userExists) > 0){
          $this->response['data']['visited_places'] = Visitedplace::where('account_id', '=', $userExists[0]['id'])->get();
          $this->response['data']['temperature_with_location'] = DB::table('temperatures')
            ->join('temperature_locations', 'temperatures.id','=', 'temperature_locations.temperature_id')
            ->select('temperatures.id', 'temperatures.account_id', 'temperatures.added_by', 'temperatures.value', 'temperatures.remarks', 'temperature_locations.longitude', 'temperature_locations.latitude', 'temperature_locations.route', 'temperature_locations.locality', 'temperature_locations.country', 'temperature_locations.region')
            ->where('account_id', '=', $userExists[0]['id'])
            ->get();
          $this->response['data']['rides'] = Ride::where('account_id', '=', $userExists[0]['id'])->get();
        }else{
          $this->response['error'] = 'User not found';
        }
      }else{
        $this->response['error'] = 'Agent not found';
      }
      return $this->response();
    }

    public function getStatus(Request $request){
      $data = $request->all();
      $accountId = $data['id'];
      $status = $this->getStatusByAccountId($accountId);
      $this->response['data'] = array(
        'status' => $status['status'],
        'status_from' => $status['status_from'],
        'status_label' => $status['status_label']
      );
      return $this->response();
    }

    public function getStatusByAccountId($accountId){
      /**
       * PRIORITY LEVEL: patient > visited places > transportation > temperature
       */
      $radius = env('RADIUS');
      $specified_days = env('SPECIFIED_DAYS');
      if (!isset($radius) || !isset($specified_days)) {
        throw new \Exception('No env variable for "RADIUS" or "SPECIFIED_DAYS');
      }

      /**
       * Initialize status for all params
       */
      $priorityStatus = array('death','positive','pui','pum','negative');
      $statuses = array(
        'patient' => 'negative',
        'visited_places' => 'negative',
        'transportation' => 'negative',
        'temperature' => 'negative'
      );
      $specifiedDate = Carbon::now()->subDays(intval($specified_days));
      /**
       * Fetch records for all params
       */
      $now = Carbon::parse(Carbon::now()->format("Y-m-d H:i:s"));
      $patientRecord = Patient::where('account_id', '=', $accountId)->first();

      if ($patientRecord !== null) {
        /**
         * Check status for patient (if exist)
         */
        $patientRecordDate = Carbon::Parse(Carbon::createFromFormat('Y-m-d H:i:s', $patientRecord->created_at)->format("Y-m-d H:i:s"));
        $daysAgo = $patientRecordDate->diffInDays($now);
        if ($daysAgo < $specified_days && $patientRecord->status === 'positive') {
          return array(
            'status' => 'positive',
            'status_from' => 'patient',
            'status_label' => 'positive'
          );
        } else if ($daysAgo < $specified_days) {
          $statuses['patient'] = $patientRecord->status;
        }
      }
      
      $visitedPlacesRecord = VisitedPlace::where('account_id', '=', $accountId)
      ->where('date', '>', $specifiedDate->format('Y-m-d'))
      ->get();
      if ($visitedPlacesRecord->count() > 0) {
        /**
         * Check status for visited place
         */
        foreach ($visitedPlacesRecord as $record) {
          $visited_place_status = app($this->tracingPlaceController)->getStatus($record, $radius);
          if (array_search($visited_place_status, $priorityStatus) < array_search($statuses['visited_places'], $priorityStatus)) {
            $statuses['visited_places'] = $visited_place_status;
          }
        }
      }
      
      $transportationRecord = Ride::where('account_id', '=', $accountId)
      ->where('payload', '=', 'qr')
      ->where('created_at', '>', $specifiedDate)
      ->get();
      if ($transportationRecord->count() > 0) {
        /**
         * Check status for transportations
         */
        foreach ($transportationRecord as $record) {
          $transpo_status = app($this->rideController)->checkQrRoute($record);
          if (array_search($transpo_status, $priorityStatus) < array_search($statuses['transportation'], $priorityStatus)) {
            $statuses['transportation'] = $transpo_status;
          }
        }
      }

      $temperatureRecord = Temperature::where('account_id', '=', $accountId)
      ->where('created_at', '>', $specifiedDate)
      ->where('value', '>', 37)
      ->get();

      if ($temperatureRecord->count() > 0) {
        /**
         * Check status for temperature
         */
        $statuses['temperature'] = 'positive';
      }

      $status['status'] = 'negative';
      $status['status_from'] = 'No record';
      $status['status_label'] = $this->getStatusLabel($status['status']);
      foreach ($statuses as $key => $value) {
        if (array_search($value, $priorityStatus) < array_search($status['status'], $priorityStatus)) { 
          $status['status'] = $value;
          $status['status_from'] = $key;
          $status['status_label'] = $this->getStatusLabel($value, $key);
        }
      }

      return array(
        'status' => $status['status'],
        'status_from' => $status['status_from'],
        'status_label' => $status['status_label']
      );
    }

    public function getStatusLabel($status, $from = null) {
      $specified_days = env('SPECIFIED_DAYS');
      if (!isset($specified_days)) {
        throw new \Exception('No env variable for "SPECIFIED_DAYS');
      }
      $template = ' last $days days';
      $days = array(
        '$days' => $specified_days
      );
      
      switch ($status) {
        case 'positive':
          if ($from === 'patient') {
            return 'Positive patient for the' . strtr($template, $days);
          } else if ($from === 'temperature') {
            return 'High temperature in the past days';
          } else {
            return 'In contact with POSITIVE' . strtr($template, $days);
          }
        case 'pui':
          return 'In contact with PUI' . strtr($template, $days);
        case 'pum':
          return 'In contact with PUM' . strtr($template, $days);
        default:
          return 'In contact with NEGATIVE' . strtr($template, $days);
      }
    }
}
