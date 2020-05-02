<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transportation;
use App\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class TransportationController extends APIController
{

  public $rideController = 'App\Http\Controllers\RideController';
  function __construct(){
    $this->model = new Transportation();
    $this->notRequired = array(
      'number'
    );
  }

  public function retrieveTracing(Request $request){
    if($this->checkAuthenticatedUser(true) == false){
      return $this->response();
    }
    $data = $request->all();
    $transportion = DB::table('transportations AS T1')
      ->join('locations AS T2', 'T2.account_id', '=', 'T1.account_id')
      ->where('T2.locality', 'like', $data['locality'])
      ->where('T2.region', 'like', $data['region'])
      ->where('T2.country', 'like', $data['country'])
      ->whereNull('T2.deleted_at')
      ->whereNull('T1.deleted_at')
      ->orderBy('T1.'.$data['sort']['column'], $data['sort']['value'])
      ->select(['T1.*', 'T2.route', 'T2.locality', 'T2.region', 'T2.country', 'T2.code'])
      ->get();

    $results = json_decode($transportion, true);
    $i = 0;
    foreach ($results as $key) {
      $results[$i]['account'] = $this->retrieveAccountDetails($key['account_id']);
      $status = app($this->rideController)->checkQrRoute(
        array(
          'transportation_id' => $key['id']
        )
      );
      $results[$i]['status'] = $status;
      $results[$i]['status_label'] = $status != 'negative' ? 'IN CONTACT WITH '.$status.' THE LAST '.env('SPECIFIED_DAYS').'DAYS' : 'CLEAR THE LAST '.env('SPECIFIED_DAYS').' DAYS';
      $results[$i]['remarks'] = null;
      $results[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $i++;
    }
    $this->response['data'] = $results;
    return $this->response();
  }

  public function update(Request $request){
    if($this->checkAuthenticatedUser(true) == false){
      return $this->response();
    }
    $data = $request->all();
    Transportation::where('id', '=', $data['id'])->update(
      array(
        'updated_at' => Carbon::now()
      )
    );
    $this->response['data'] = true;
    return $this->response();
  }

  public function getByParams($column, $value){
    $result = Transportation::where($column, '=', $value)->orderBy('updated_at', 'desc')->get();
    return (sizeof($result) > 0) ? $result[0] : null;
  }
}
