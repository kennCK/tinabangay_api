<?php

namespace App\Http\Controllers;

use App\BrgyCode;
use App\Location;
use App\Symptom;
use App\VisitedPlace;
use App\Temperature;
use App\HealthDeclaration;
use Increment\Account\Models\Account;
use Increment\Account\Models\SubAccount;
use Increment\Account\Models\AccountInformation;
use Increment\Account\Models\BillingInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CustomController extends APIController
{
    function __construct(){
      $this->model = new Account();
      $this->validation = array(  
        "email" => "unique:accounts",
        "username"  => "unique:accounts"
      );
    }

    public function getScannedAccountStatus (Request $request) {
      $result = array(
        'account' => null,
        'account_information' => null,
        'temperature' => null,
        'location' => null,
        'health_declaration' => null,
        'overall_status' => null,
        'linked_account' => null
      );
      $data = $request->all();
      $scannedAccountCode =  $data['code'];
      $merchantId = $data['merchant_id'];

      $scannedAccount = Account::where('code', '=', $scannedAccountCode)->first();
      if (sizeof($scannedAccount) > 0) {
        $result['account'] = $scannedAccount;
        $result['account_information'] = app('Increment\Account\Http\AccountInformationController')->getAccountInformation($scannedAccount['id']);
        $result['temperature'] = Temperature::where('account_id', '=', $scannedAccount['id'])->orderBy('created_at', 'desc')->first();
        $result['health_declaration'] = HealthDeclaration::where('owner', '=', $merchantId)->where('account_id', '=', $scannedAccount['id'])->orderBy('updated_at', 'desc')->first();
        $result['location'] = app('App\Http\Controllers\LocationController')->getByParamsWithCode('account_id', $scannedAccount['id']);
        $result['overall_status'] = app('App\Http\Controllers\TracingController')->getStatusByAccountId($scannedAccount['id']);
        $result['linked_account'] = app('App\Http\Controllers\LinkedAccountController')->getLinkedAccount('account_id', $scannedAccount['id']);
        
        if ($result['temperature'] != null) {
          $result['temperature']['created_at_human'] = $this->daysDiffDateTime($result['temperature']['created_at']);
        }
        if ($result['health_declaration'] != null) {
          $result['health_declaration']['updated_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result['health_declaration']['updated_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
        }
      }

      return $result;
    }

    public function importAccounts (Request $request) {
      $data = $request->all();
      if (sizeof($data['entries']) > 0) {
        foreach ($data['entries'] as $entry) {
          /**
           * check if valid uacs_brgy_code
           */
          $brgy_code = BrgyCode::where('code', '=', $entry['uacs_brgy_code'])->first();
          if (!$brgy_code) {
            $this->response['errorMessage'] = 'Invalid uacs_brgy_code: ' . $entry['uacs_brgy_code'] . '.';
            return $this->response();
          }

          $dataAccount = array(
            'code'          => $this->generateCode(),
            'password'      => Hash::make($entry['password']),
            'status'        => 'NOT_VERIFIED',
            'email'         => $entry['email'],
            'username'      => $entry['username'],
            'account_type'  => $entry['account_type'],
            'created_at'    => Carbon::now()
          );
          $this->model = new Account();
          $this->insertDB($dataAccount, true);
          
          /**
           * return if error
           */
          $errorStatus = $this->response['error'];
          if (!empty($errorStatus) && $errorStatus['status'] === 100) {
            if (!empty($errorStatus['message']['username'])) {
              $this->response['errorMessage'] = 'The username \'' . $dataAccount['username'] . '\' has already been taken.';
              return $this->response();
            }
            if (!empty($errorStatus['message']['email'])) {
              $this->response['errorMessage'] = 'The email \'' . $dataAccount['email'] . '\' has already been taken.';
              return $this->response();
            }
          }

          $accountId = $this->response['data'];
          if ($accountId) {
            /**
             * insert account_informations
             */
            $this->createDetails($accountId, $entry);
            /**
             * insert sub_accounts
             */
            if(env('SUB_ACCOUNT') == true){
                $status = 'USER';
                if($entry['status'] == 'AGENCY_BRGY'){
                  app('Increment\Account\Http\SubAccountController')->createByParams($entry['creator'], $accountId, $status);
                }
            }
            /**
             * insert locations
             */
            $locations = array(
              'code'        => $entry['uacs_brgy_code'],
              'account_id'  => $accountId,
              'longitude'   => $brgy_code['longitude'],
              'latitude'    => $brgy_code['latitude'],
              'route'       => $brgy_code['route'],
              'locality'    => $brgy_code['locality'],
              'country'     => $brgy_code['country'],
              'region'      => $brgy_code['region'],
              'created_at'  => Carbon::now()
            );
            Location::insert($locations);
          }

        } 
      }

      return $this->response();
    }

    public function importSymptoms(Request $request) {
      $data = $request->all();
      $symptomsArr = array();
      if (sizeof($data['entries']) > 0) {
        foreach ($data['entries'] as $entry) {
          /**
           * check if username exists
           */
          $username = Account::where('username', '=', $entry['username'])->first();
          if (!$username) {
            $this->response['errorMessage'] = 'Username \'' . $entry['username'] . '\' does not exists.';
            return $this->response();
          }

          /**
           * check user if member of creator
           */
          $member = SubAccount::where('account_id', '=', $entry['creator_id'])
                              ->where('member', '=', $username->id)->first();
          if (!$member) {
            $this->response['errorMessage'] = 'Sorry, the username \'' . $entry['username'] . '\' is not your member.';
            return $this->response();
          }

          $dataSymptoms = array(
            'account_id'  => $username->id,
            'type'        => $entry['type'],
            'remarks'     => $entry['remarks'],
            'date'        => $entry['date'],
            'created_at'  => Carbon::now()
          );

          array_push($symptomsArr, $dataSymptoms);
        } 
      }

      if (sizeof($symptomsArr) > 0) {
        Symptom::insert($symptomsArr);
        $this->response['data'] = sizeof($symptomsArr);
      } 

      return $this->response();
    }

    public function importVisitedPlaces(Request $request) {
      $data = $request->all();
      $visitedPlacesArr = array();
      if (sizeof($data['entries']) > 0) {
        foreach ($data['entries'] as $entry) {
          /**
           * check if username exists
           */
          $username = Account::where('username', '=', $entry['username'])->first();
          if (!$username) {
            $this->response['errorMessage'] = 'Username \'' . $entry['username'] . '\' does not exists';
            return $this->response();
          }

          /**
           * check user if member of creator
           */
          $member = SubAccount::where('account_id', '=', $entry['creator_id'])
                              ->where('member', '=', $username->id)->first();
          if (!$member) {
            $this->response['errorMessage'] = 'Sorry, the username \'' . $entry['username'] . '\' is not your member.';
            return $this->response();
          }

          /**
           * get brgy code data
           */
          $brgy_code = BrgyCode::where('code', '=', $entry['brgy_code'])->first();
          if (!$brgy_code) {
            $this->response['errorMessage'] = 'Barangay code \'' . $entry['brgy_code'] . '\' not found';
            return $this->response();
          }

          $visitedPlace = array(
            'account_id'  => $username->id,
            'longitude'   => $brgy_code->longitude,
            'latitude'    => $brgy_code->latitude,
            'route'       => $brgy_code->route,
            'locality'    => $brgy_code->locality,
            'country'     => $brgy_code->country,
            'region'      => $brgy_code->region,
            'date'        => $entry['date'],
            'time'        => $entry['time'],
            'created_at'  => Carbon::now()
          );

          array_push($visitedPlacesArr, $visitedPlace);
        } 
      }

      if (sizeof($visitedPlacesArr) > 0) {
        VisitedPlace::insert($visitedPlacesArr);
        $this->response['data'] = sizeof($visitedPlacesArr);
      }

      return $this->response();
    }

    public function setBrgyAddress(Request $request) {
      $data = $request->all();
      $prevCode = $data['params']['currentCode'];
      $this->response['invalidCode'] = false;

      $brgy_code = BrgyCode::where('code', '=', $data['params']['brgyCode'])->first();
      if (!$brgy_code) {
        $this->response['invalidCode'] = true;
        return $this->response();
      }

      if ($prevCode) {
        $location = array(
          'code'        => $data['params']['brgyCode'],
          'account_id'  => $data['accountId'],
          'longitude'   => $brgy_code['longitude'],
          'latitude'    => $brgy_code['latitude'],
          'route'       => $brgy_code['route'],
          'locality'    => $brgy_code['locality'],
          'country'     => $brgy_code['country'],
          'region'      => $brgy_code['region'],
          'updated_at'  => Carbon::now()
        );
        Location::where('account_id', '=', $data['accountId'])->update($location);
      } else {
        $location = array(
          'code'        => $data['params']['brgyCode'],
          'account_id'  => $data['accountId'],
          'longitude'   => $brgy_code['longitude'],
          'latitude'    => $brgy_code['latitude'],
          'route'       => $brgy_code['route'],
          'locality'    => $brgy_code['locality'],
          'country'     => $brgy_code['country'],
          'region'      => $brgy_code['region'],
          'created_at'  => Carbon::now()
        );
        Location::insert($location);
      }
  
      return $this->response();
    }

    public function createDetails($accountId, $entry){
      $info = new AccountInformation();
      $info->account_id = $accountId;
      $info->first_name = $entry['first_name'];
      $info->middle_name = $entry['middle_name'];
      $info->last_name = $entry['last_name'];
      $info->created_at = Carbon::now();
      $info->save();

      $billing = new BillingInformation();
      $billing->account_id = $accountId;
      $billing->created_at = Carbon::now();
      $billing->save();
      if(env('NOTIFICATION_SETTING_FLAG') == true){
        app('App\Http\Controllers\NotificationSettingController')->insert($accountId);
      }
    }

    public function generateCode(){
      $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 32);
      $codeExist = Account::where('code', '=', $code)->get();
      if(sizeof($codeExist) > 0){
        $this->generateCode();
      }else{
        return $code;
      }
    }
}
