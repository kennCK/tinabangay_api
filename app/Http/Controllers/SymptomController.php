<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Symptom;
use Illuminate\Support\Facades\DB;
class SymptomController extends APIController
{
  function __construct(){
    $this->model = new Symptom();
    $this->notRequired = array(
      'remarks'
    );
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); 
    $i = 0;
    $data = $this->response['data'];
    foreach ($data as $key) {
      $data[$i]['date_human'] = $this->daysDiffByDate($key['date']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }

  public function retrieveTracing(Request $request){
    if($this->checkAuthenticatedUser(true) == false){
      return $this->response();
    }
    $data = $request->all();
    $symptoms = DB::table('symptoms AS T1')
      ->join('locations AS T2', 'T2.account_id', '=', 'T1.account_id')
      ->where('T2.locality', 'like', $data['locality'])
      ->where('T2.region', 'like', $data['region'])
      ->where('T2.country', 'like', $data['country'])
      ->whereNull('T2.deleted_at')
      ->whereNull('T1.deleted_at')
      ->orderBy('T1.'.$data['sort']['column'], $data['sort']['value'])
      ->select(['T1.*','T2.route','T2.locality','T2.country','T2.region', 'T2.code'])
      ->get();
    $results = json_decode($symptoms, true);
    $i = 0;
    foreach ($results as $key) {
      $results[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $results[$i]['date_human'] = $this->daysDiffByDate($key['date']);
      $i++;
    }
    $this->response['data'] = $results;
    return $this->response();
  }
}
