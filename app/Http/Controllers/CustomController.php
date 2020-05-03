<?php

namespace App\Http\Controllers;

use App\BrgyCode;
use App\Location;
use App\Symptom;
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
      if (sizeof($data['entries']) > 0) {
        foreach ($data['entries'] as $entry) {
          /**
           * check if username exists
           */
          $username = Account::where('username', '=', $entry['username'])->first();
          if (!$username) {
            $this->response['errorMessage'] = 'Username \'' . $entry['username'] . '\' not found';
            return $this->response();
          }

          $dataSymptoms = array(
            'account_id'  => $username->id,
            'type'        => $entry['type'],
            'remarks'     => $entry['remarks'],
            'date'        => $entry['date'],
            'created_at'  => Carbon::now()
          );

          $this->model = new Symptom();
          $this->insertDB($dataSymptoms, true);
        } 
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
