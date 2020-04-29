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
      /**
       * PRIORITY: patient > visited places > transportation > temperature
       */
      $radius = env('RADIUS');
      if (!isset($radius)) {
        throw new \Exception('No env variable for "RADIUS"');
      }

      if (isset($data['radius'])) {
        $radius = $data['radius'];
      }

      $priorityStatus = array('death','positive','pui','pum','negative');
      $data = $request->all();
      $accountId = $data['id'];
      $statuses = array(
        'patient' => 'negative',
        'visited_places' => 'negative',
        'transportation' => 'negative',
        'temperature' => 'negative'
      );
      
      $now = Carbon::parse(Carbon::now()->format("Y-m-d H:i:s"));
      $patientRecord = Patient::where('account_id', '=', $accountId)->first();
      $visitedPlacesRecord = VisitedPlace::where('account_id', '=', $accountId)->get();

      if ($patientRecord !== null) {
        $patientRecordDate = Carbon::Parse(Carbon::createFromFormat('Y-m-d H:i:s', $patientRecord->created_at)->format("Y-m-d H:i:s"));
        $daysAgo = $patientRecordDate->diffInDays($now);
        if ($daysAgo < 14 && $patientRecord->status === 'positive') {
          $this->response['data'] = array(
            'from' => 'patient',
            'status' => 'positive'
          );
          return $this->response();
        } else if ($daysAgo < 14) {
          $statuses['patient'] = $patientRecord->status;
        }
      } else if ($visitedPlacesRecord->count() > 0) {
        foreach ($visitedPlacesRecord as $place) {
          $visited_place_status = app($this->tracingPlaceController)->getStatus($place, $radius);
          if (array_search($visited_place_status, $priorityStatus) < array_search($statuses['visited_places'], $priorityStatus)) {
            $statuses['visited_places'] = $visited_place_status;
          }
        }
      }


      $status['key'] = null;
      $status['status'] = 'negative';
      foreach ($statuses as $key => $value) {
        if (array_search($value, $priorityStatus) < array_search($status['status'], $priorityStatus)) { 
          $status['key'] = $key;
          $status['status'] = $value;
        }
      }

      $this->response['data'] = array(
        'from' => $status['key'],
        'status' => $status['status']
      );
      return $this->response();
      // return $status;
    }

    public function getStatusByAccountId($accountId){
      return array(
        'status' => 'negative',
        'status_label' => 'IN CONTACT WITH NEGATIVE LAST 14 DAYS'
      );
    }
}
