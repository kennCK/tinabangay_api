<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Patient;
use App\VisitedPlace;
class GoogleSheetController extends APIController
{
  
  public $visitedPlacesClass = 'App\Http\Controllers\VisitedPlaceController';
  public function patients(Request $request){
    $client = new \Google_Client();
    $client->setApplicationName('Increment API');
    $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
    $client->setAccessType('offline');
    $client->setAuthConfig(__DIR__.'/credentials.json');
    $service = new \Google_Service_Sheets($client);
    $spreedSheetID = '1TDoGD6g_-Y_ASN0RrergalWUSwAMoIcLacTX5QxiAtA';
    
    $data = $request->all();

    $this->model = new Patient();
    $this->retrieveDB($data);
    
    $i = 0;
    
    $data = $this->response['data'];
    foreach ($data as $key) {
      if($key['account_id'] > 0){
        $data[$i]['places'] = app($this->visitedPlacesClass)->getByParams('account_id', $key['account_id']);
      }else{
        $data[$i]['places'] = app($this->visitedPlacesClass)->getByParams('patient_id', $key['id']);
      }
      $place = sizeof($data[$i]['places']) > 0 ? $data[$i]['places'][0] : null;
      $range = 'System Data';
      $route =  $place ? $place['route'] : 'test';
      $locality =  $place ? $place['locality'] : 'test';
      $region =  $place ? $place['region'] : 'test';
      $country =  $place ? $place['country'] : 'test';
      $longitude =  $place ? $place['longitude'] : 'test';
      $latitude =  $place ? $place['latitude'] : 'test';
      $date =  $place ? $place['date'] : 'test';
      $time =  $place ? $place['time'] : 'test';
      $code = $key['code'] ? $key['code'] : 'test';
      $status = $key['status'] ? $key['status'] : 'test';
      $remarks = $key['remarks'] ? $key['remarks'] : 'test';
      $source = $key['source'] ? $key['source'] : 'test';
      $values = [
        [$code, $status, $remarks, $source, $route, $locality, $region, $country, $longitude, $latitude, $date, $time]
      ];
      // $this->response['data'] = $values;
      // return $this->response();
      $body = new \Google_Service_Sheets_ValueRange([
        'values' => $values
      ]);

      $params = [
        'valueInputOption' => 'USER_ENTERED'
      ];

      $result = $service->spreadsheets_values->append($spreedSheetID, $range, $body, $params);
      $i++;
    }
    $this->response['data'] = $spreedSheetID;
    return $this->response();
  }
}
