<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use App\UserInformation;
use App\LinkedAccount;
use App\Account;
use App\Temperature;
use App\Ride;

use DB;
use Carbon\Carbon;

class TracingController extends APIController
{
    public $tracingPlaceController = 'App\Http\Controllers\TracingPlaceController';
    public $rideController = 'App\Http\Controllers\RideController';

    /**
     * ONLY IF THERE ARE LINKED ACCOUNTS
     * flag 0 => start of recursion
     * flag 1 => recursion running
     * flag 2 => end of recursion
     */
    private $flag = 0;
    private $linkedAccountsStatus;

    public function tree(){
      $placesList = VisitedPlace::with('patients','userInfo')->get();
      $this->retrieveDB($placesList);
      return $this->response();
    }

    public function getHistory($username, $agent_id){
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
      if($this->checkAuthenticatedUser(true) == false){
        return $this->response();
      }
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
      $radius = env('RADIUS');
      $specified_days = env('SPECIFIED_DAYS');
      if (!isset($radius) || !isset($specified_days)) {
        throw new \Exception('No env variable for "RADIUS" or "SPECIFIED_DAYS');
      }

      /**
       * PRIORITY LEVEL: patient > visited places > transportation > temperature
       * Initialize status for all params
       */
      $priorityStatus = array('death','positive','pui','pum','negative');
      $statuses = array(
        'visited_places' => 'negative',
        'transportation' => 'negative',
        'linked_accounts' => 'negative',
      );
      $now = Carbon::parse(Carbon::now()->format("Y-m-d H:i:s"));
      $specifiedDate = Carbon::now()->subDays(intval($specified_days));
      
      /**
       * Check status for patient (if exist)
       */
      $patientRecord = Patient::where('account_id', '=', $accountId)->first();
      if ($patientRecord !== null) {
        $patientRecordDate = Carbon::Parse(Carbon::createFromFormat('Y-m-d H:i:s', $patientRecord->created_at)->format("Y-m-d H:i:s"));
        $daysAgo = $patientRecordDate->diffInDays($now);
        if ($daysAgo < $specified_days) {
          if ($patientRecord->status !== 'negative') {
            return array(
              'status' => $patientRecord->status,
              'status_from' => 'patient',
              'status_label' => $this->getStatusLabel($patientRecord->status, 'patient')
            );
          }
        }
      }
      
      /**
       * Check status for visited place
       */
      $visitedPlacesRecord = VisitedPlace::where('account_id', '=', $accountId)
      ->where('date', '>', $specifiedDate->format('Y-m-d'))
      ->get();
      if ($visitedPlacesRecord->count() > 0) {
        foreach ($visitedPlacesRecord as $record) {
          $visited_place_status = app($this->tracingPlaceController)->getStatus($record, $radius);
          if (array_search($visited_place_status, $priorityStatus) < array_search($statuses['visited_places'], $priorityStatus)) {
            $statuses['visited_places'] = $visited_place_status;
          }
        }

        if ($statuses['visited_places'] !== 'negative') {
          return array(
            'status' => $statuses['visited_places'],
            'status_from' => 'visited_places',
            'status_label' => $this->getStatusLabel($statuses['visited_places'], 'visited_places')
          );
        }
      }
      
      /**
       * Check status for transportations
       */
      $transportationRecord = Ride::where('account_id', '=', $accountId)
      ->where('payload', '=', 'qr')
      ->where('created_at', '>', $specifiedDate)
      ->get();
      if ($transportationRecord->count() > 0) {
        foreach ($transportationRecord as $record) {
          $transpo_status = app($this->rideController)->checkQrRoute($record);
          if (array_search($transpo_status, $priorityStatus) < array_search($statuses['transportation'], $priorityStatus)) {
            $statuses['transportation'] = $transpo_status;
          }
        }

        if ($statuses['transportation'] !== 'negative') {
          return array(
            'status' => $statuses['transportation'],
            'status_from' => 'transportation',
            'status_label' => $this->getStatusLabel($statuses['transportation'], 'transportation')
          );
        }
      }

      /**
       * Check status for temperature
       */
      $temperatureRecord = Temperature::where('account_id', '=', $accountId)
      ->where('created_at', '>', $specifiedDate)
      ->where('value', '>', 37)
      ->get();
      if ($temperatureRecord->count() > 0) {
        return array(
          'status' => 'positive',
          'status_from' => 'temperature',
          'status_label' => $this->getStatusLabel('positive', 'temperature')
        );
      }

      /**
       * Check status for linked accounts
       */
      if ($this->flag == 0) {
        $this->linkedAccountsStatus = $this->getLinkedAccountsStatus($accountId);
      }
      
      /**
       * return status for linked accounts
       */
      if ($this->flag == 2) {
        /**
         * ===== FOR TESTING =====
         * 1) uncomment line 191 to return all linked accounts status
         * 2) comment out line 234 and 238 to loop through all linked accounts
         */

        // return $this->linkedAccountsStatus;
        foreach($this->linkedAccountsStatus as $key => $val) {
          if ($val['status'] === 'positive') {
            return array(
              'status' => 'positive',
              'status_from' => 'linked_accounts',
              'status_label' => $this->getStatusLabel('positive', 'linked_accounts')
            );
          }
          if (array_search($val['status'], $priorityStatus) < array_search($statuses['linked_accounts'], $priorityStatus)) {
            $statuses['linked_accounts'] = $val['status'];
          }
        }

        if ($statuses['linked_accounts'] !== 'negative') {
          return array(
            'status' => $statuses['linked_accounts'],
            'status_from' => 'linked_accounts',
            'status_label' => $this->getStatusLabel($statuses['linked_accounts'], 'linked_accounts')
          );
        }
      }

      return array(
        'status' => 'negative',
        'status_from' => 'No record',
        'status_label' => $this->getStatusLabel('negative')
      );
    }

    public function getLinkedAccountsStatus($accountId) {
      /**
       * Check flag meaning in line 23-25
       */
      $this->flag = 1;
      $priorityStatus = array('death','positive','pui','pum','negative');
      $linkedAccounts = LinkedAccount::where('owner', '=', $accountId)->orWhere('account_id', '=', $accountId)->get();
      if ($linkedAccounts->count() > 0) {
        $tempArr = array();
        // loop stops if 'positive' status is found
        foreach ( $linkedAccounts as $index => $obj) {
          if ($obj->owner === $accountId) {
            $tempArr[$index] = $this->getStatusByAccountId($obj->account_id);
            if ($tempArr[$index]['status'] === 'positive') break;
          }
          else if ($obj->account_id === $accountId) {
            $tempArr[$index] = $this->getStatusByAccountId($obj->owner);
            if ($tempArr[$index]['status'] === 'positive') break;
          }
        }
        $this->flag = 2;
        return $tempArr;
      }
    }

    public function getStatusLabel($status, $from = null) {
      $specified_days = env('SPECIFIED_DAYS');
      if (!isset($specified_days)) {
        throw new \Exception('No env variable for "SPECIFIED_DAYS');
      }
      $template = ' LAST $days DAYS';
      $days = array(
        '$days' => $specified_days
      );

      if ($from === 'patient') {
        return strtoupper($status) . ' PATIENT FOR THE' . strtr($template, $days);
      }

      if ($from === 'linked_accounts') {
        return 'IN CONTACT WITH ' . strtoupper($status) . ' LINKED ACCOUNT' . strtr($template, $days);
      }
      
      switch ($status) {
        case 'positive':
          if ($from === 'temperature') {
            return 'HIGH TEMPERATURE IN THE PAST 14 DAYS';
          } else {
            return 'IN CONTACT WITH POSITIVE' . strtr($template, $days);
          }
        case 'pui':
          return 'IN CONTACT WITH PUI' . strtr($template, $days);
        case 'pum':
          return 'IN CONTACT WITH PUM' . strtr($template, $days);
        default:
          return 'CLEARED THE' . strtr($template, $days);
      }
    }
}
