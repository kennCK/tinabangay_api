<?php

namespace App\Http\Controllers;

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
            $this->createDetails($accountId, $entry);
            if(env('SUB_ACCOUNT') == true){
                $status = $entry['status'];
                if($status == 'AGENCY_BRGY'){
                  app('Increment\Account\Http\SubAccountController')->createByParams($accountId, $entry['member'], $status);
                }
            }
          }
        } 
      }

      return $this->response();
    }

    public function retrieve(Request $request){
      $data = $request->all();
      $this->model = new SubAccount();
      $this->retrieveDB($data);
      $result = $this->response['data'];

      if(sizeof($result) > 0){
        $i = 0;
        foreach ($result as $key) {
          $this->response['data'][$i]['account'] = $this->retrieveAccountDetails($result[$i]['account_id']);
          $i++;
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
