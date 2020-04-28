<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LinkedAccount;
class LinkedAccountController extends APIController
{
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
}
