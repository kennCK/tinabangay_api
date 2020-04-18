<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Patient;
use App\VisitedPlace;
use Carbon\Carbon;
class PatientController extends APIController
{
  public $visitedPlacesClass = 'App\Http\Controllers\VisitedPlaceController';
  function __construct(){
    $this->model = new Patient();
    $this->notRequired = array('remarks', 'account_id', 'code', 'source');
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); 
    $i = 0;
    $data = $this->response['data'];
    foreach ($data as $key) {
      $data[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $data[$i]['places'] = isset($key['account_id']) ? app($this->visitedPlacesClass)->getByParams('account_id', $key['account_id']) : app($this->visitedPlacesClass)->getByParams('patient_id', $key['id']);
      $data[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }

  public function summary(Request $request){
    $this->response['data'] = array(
      'positive' => Patient::where('status', '=', 'positive')->count(),
      'pui'     => Patient::where('status', '=', 'pui')->count(),
      'pum'     => Patient::where('status', '=', 'pum')->count(),
      'death'     => Patient::where('status', '=', 'death')->count(),
      'negative'     => Patient::where('status', '=', 'negative')->count(),
      'recovered'     => Patient::where('status', '=', 'recovered')->count()
    );
    return $this->response();
  }

  public function create(Request $request){
    $data = $request->all(); 
    $accountId = isset($data['account_id']) ? $data['account_id'] : null;
    $patientCode = isset($data['code']) ? $data['code'] : null;
    $source = isset($data['source']) ? $data['source'] : null;
    $newStatus = $data['status'];
    $previousAccount = isset($accountId) ? Patient::where('account_id', '=', $accountId)->orderBy('created_at', 'desc')->get() : array();
    $previousCode = isset($patientCode) ? Patient::where('code', '=', $patientCode)->orderBy('created_at', 'desc')->get() : array();
    if(sizeof($previousAccount) > 0 && $previousAccount[0]['status'] == $newStatus || sizeof($previousCode) > 0 && $previousCode[0]['status'] == $newStatus){
      $this->response['data'] = null;
      $this->response['error'] = "Duplicate Entry!";
    }else{      
      $this->insertDB($data);
    }
    return $this->response();
  }

  public function retrieveNotifications(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    $result = $this->response['data'];
    if(sizeof($result) > 0){
      $i = 0;
      foreach ($result as $key) {
        $result[$i]['created_at_human'] =  $this->daysDiffDateTime($result[$i]['created_at']);
        $i++;
      }
    }
    $this->response['data'] = $result;
    return $this->response();
  }
}
