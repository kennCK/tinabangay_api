<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LinkedAccount;
class LinkedAccountController extends APIController
{
  public $tracingController = 'App\Http\Controllers\TracingController';
  function __construct(){
    $this->model = new LinkedAccount();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    $data = $this->response['data'];
    $i = 0;
    foreach ($data as $key) {
      $data[$i]['owner_account'] = $this->retrieveAccountDetails($key['owner']);
      $data[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $data[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $data[$i]['assigned_location'] = app('App\Http\Controllers\LocationController')->getAssignedLocation('account_id', $key['account_id']);
      $data[$i]['address'] = app('App\Http\Controllers\LocationController')->getByParamsWithCode('account_id', $key['account_id']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }

  public function retrieveEmployees(Request $request){
    $condition = $request->all();
    $this->retrieveDB($condition);
    $data = $this->response['data'];
    $i = 0;
    foreach ($data as $key) {
      unset($data[$i]['created_at']);
      unset($data[$i]['updated_at']);
      unset($data[$i]['deleted_at']);
      $data[$i]['account'] = $this->retrieveName($key['account_id']);
      $data[$i]['account_owner'] = $this->retrieveName($key['owner']);
      $data[$i]['accoun_id'] = $key['account_id'];
      $data[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $data[$i]['assigned_location'] = $this-> retrieveAssignedLocatio($key['account_id']);
      $data[$i]['address'] = $this->retrieveAddress($key['account_id']);
      $i++;
    }
    $this->response['data'] = $data;
    $this->response['size'] = LinkedAccount::where($condition['condition'][0]['column'], '=', $condition['condition'][0]['value'])->count();
    return $this->response();
  }

  public function getLinkedAccount($column, $value){
    $result = LinkedAccount::where($column, '=', $value)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }

  public function retrieveTracing(Request $request){
    $data = $request->all();

    $radius = env('RADIUS');
    if (!isset($radius)) {
      throw new \Exception('No env variable for "RADIUS"');
    }

    if (isset($data['radius'])) {
      $radius = $data['radius'];
    }

    $this->retrieveDB($data); // store to 
    $data = $this->response['data'];
    $i = 0;
    foreach ($data as $key) {
      $status = app($this->tracingController)->getStatusByAccountId($data[$i]['account_id']);
      $data[$i]['status'] =  $status['status'];
      $data[$i]['status_from'] =  $status['status_from'];
      $data[$i]['status_label'] =  $status['status_label'];
      $data[$i]['account'] = $this->retrieveAccountDetailsOnlyImportant($data[$i]['account_id']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }

  public function retrieveName($accountId){
    $result = app('Increment\Account\Http\AccountController')->retrieveById($accountId);
    if(sizeof($result) > 0){
      $result[0]['information'] = app('Increment\Account\Http\AccountInformationController')->getAccountInformation($accountId);
      if($result[0]['information'] != null && $result[0]['information']['first_name'] != null && $result[0]['information']['last_name'] != null){
        $account = array(
          'names' => $result[0]['information']['first_name'].' '.$result[0]['information']['last_name'],
          'account_type' => $result[0]['account_type']
        );
        return $account;
      }
      $account = array(
        'names' => $result[0]['username'],
        'account_type' => $result[0]['account_type']
      );
      return $account;
    }else{
      return null;
    }
  }

  public function retrieveAssignedLocatio($accountId){
    $assigned_location = app('App\Http\Controllers\LocationController')->getAssignedLocation('account_id', $accountId);
    if($assigned_location != null){
      return $assigned_location->only(['id', 'account_id', 'assigned_code', 'route']);
    }
    else{
      return null;
    }
  }

  public function retrieveAddress($accountId){
    $address = app('App\Http\Controllers\LocationController')->getByParamsWithCode('account_id', $accountId);
    if($address != null){
      return $address->only(['id', 'code']);
    }else{
      return null;
    }
  }
}
