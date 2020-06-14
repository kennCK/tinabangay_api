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
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
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
      $data[$i]['account'] = $this->retrieveAccountDetails($data[$i]['account_id']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
}
