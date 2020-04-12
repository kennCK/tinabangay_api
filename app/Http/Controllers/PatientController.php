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
      $data[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $data[$i]['places'] = app($this->visitedPlacesClass)->getByParams('account_id', $key['account_id']);
      $data[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $key['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
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
    $accountId = $data['account_id'];
    $newStatus = $data['status']; 
    $previous = Patient::where('account_id', '=', $accountId)->orderBy('created_at', 'desc')->get();
    if(sizeof($previous) > 0 && $previous[0]['status'] == $newStatus){
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
        $result[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
        $i++;
      }
    }
    $this->response['data'] = $result;
    return $this->response();
  }
}
