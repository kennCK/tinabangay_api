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

  public function linking(Request $request){
    if($this->checkAuthenticatedUser(true) == false){
      return $this->response();
    }
    $data = $request->all();
    if(sizeof($data['entries']) > 0){
      foreach ($data['entries'] as $key) {
        $this->model = new Patient();
        $this->response['data'] = null;
        $this->response['error'] = null;
        $this->response['code'] =  null;

        $visitedPlacesData = array(
          'route'       => $key['route'],
          'locality'    => $key['locality'],
          'region'      => $key['region'],
          'country'     => $key['country'],
          'longitude'   => $key['longitude'],
          'latitude'    => $key['latitude'],
          'date'        => $key['date'], 
          'time'        => $key['time'],
        );

        $previousCode = Patient::where('code', '=', $key['code'])->orderBy('created_at', 'desc')->get();
        if (sizeof($previousCode) > 0) { 
          /**
           * if patient exists -> update
           */
          $patientData = array(
            'added_by'  => 1,
            'remarks'   => $key['remarks'],
            'source'    => $key['source'],
            'status'    => $key['status'],
            'updated_at'  => Carbon::now()
          );
          $visitedPlacesData['updated_at'] = Carbon::now();
          Patient::where('code', '=', $key['code'])->update($patientData);
          VisitedPlace::where('patient_id', '=', $previousCode[0]['id'])->update($visitedPlacesData);
        } else {
          /**
           * if new patient -> insert
           */
          $patientData = array(
            'added_by'  => 1,
            'code'      => $key['code'],
            'remarks'   => $key['remarks'],
            'source'    => $key['source'],
            'status'    => $key['status'],
            'created_at'  => Carbon::now()
          );
          $this->insertDB($patientData);
          if ($this->response['data'] > 0) {    
            $visitedPlacesData['patient_id'] = $this->response['data'];
            $visitedPlacesData['account_id'] = null;
            $visitedPlacesData['created_at'] = Carbon::now();
            VisitedPlace::insert($visitedPlacesData);
          }
        }                
      }
    }else{
      $this->response['data'] = null;
      $this->response['error'] = 'Empty Entries';
      $this->response['code'] =  null;
    }
    return $this->response();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data); 
    $i = 0;
    $data = $this->response['data'];
    foreach ($data as $key) {
      $data[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      if($key['account_id'] > 0){
        $data[$i]['places'] = app($this->visitedPlacesClass)->getByParams('account_id', $key['account_id']);
      }else{
        $data[$i]['places'] = app($this->visitedPlacesClass)->getByParams('patient_id', $key['id']);
      }
      
      $data[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $i++;
    }
    $this->response['data'] = $data;
    $this->response['size'] = Patient::where('deleted_at', '=', null)->count();
    return $this->response();
  }

  public function summary(Request $request){
    $data = $request->all();
    if($this->checkAuthenticatedUser(true) == false){
      return $this->response();
    }

    $search = '%';
    
    if(isset($data['locality'])){
      $search = $data['locality'] .'%';
    }

    $totalPositive = 0;
    $totalDeath = 0;
    $totalRecovered = 0;

    // patients where account_id OR code is not NULL -> 1 count per record
    $positivePatients = Patient::where('status', '=', 'positive')
                                ->where('locality', 'like', $search)
                                ->where( function($record) { 
                                    return $record->whereNotNull('account_id')->orWhereNotNull('code');
                                  })
                                ->count();
    $deathPatients = Patient::where('status', '=', 'death')
                            ->where('locality', 'like', $search)
                            ->where(function($record){
                                return $record->whereNotNull('account_id')->orWhereNotNull('code');
                              })
                            ->count();
    $recoveredPatients = Patient::where('status', '=', 'recovered')
                                ->where('locality', 'like', $search)
                                ->where(function($record){
                                    return $record->whereNotNull('account_id')->orWhereNotNull('code');
                                  })
                                ->count();

    $totalPositive += $positivePatients;
    $totalDeath += $deathPatients;
    $totalRecovered += $recoveredPatients;

    // patients where account_id AND code is NULL -> count will be based on remarks column value
    $remarksAsPositiveCounter = Patient::where('status', '=', 'positive')
                                ->where('locality', 'like', $search)
                                ->whereNull('account_id')
                                ->whereNull('code')->get();
    $remarksAsDeathCounter = Patient::where('status', '=', 'death')
                                ->where('locality', 'like', $search)
                                ->whereNull('account_id')
                                ->whereNull('code')->get();
    $remarksAsRecoveredCounter = Patient::where('status', '=', 'recovered')
                                ->where('locality', 'like', $search)
                                ->whereNull('account_id')
                                ->whereNull('code')->get();
    if (count($remarksAsPositiveCounter)) {
      foreach($remarksAsPositiveCounter as $record){
        $totalPositive += intval($record->remarks);
      }
    }
    if (count($remarksAsDeathCounter)) {
      foreach($remarksAsDeathCounter as $record){
        $totalDeath += intval($record->remarks);
      }
    }  
    if (count($remarksAsRecoveredCounter)) {
      foreach($remarksAsRecoveredCounter as $record){
        $totalRecovered += intval($record->remarks);
      }
    }

    $this->response['data'] = array(
      'positive'  => $totalPositive,
      'pui'       => Patient::where('status', '=', 'pui')->count(),
      'pum'       => Patient::where('status', '=', 'pum')->count(),
      'death'     => $totalDeath,
      'negative'  => Patient::where('status', '=', 'negative')->count(),
      'recovered' => $totalRecovered
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

  public function createSymptoms(Request $request){
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

  public function getStatusByParams($column, $value){
    $result = Patient::where($column, $value)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }
}
