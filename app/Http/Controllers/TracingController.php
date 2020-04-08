<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use App\UserInformation;

use App\Account;
use App\Temperature;
use App\Ride;

use DB;

class TracingController extends APIController
{
    public function tree(){
        $placesList = VisitedPlace::with('patients','userInfo')->get();
        $this->retrieveDB($placesList);
        return $this->response();
        
//         if(!empty($placesList)){
//           $jsonData = json_encode($placesList->toArray());
//           dd($jsonData);
//         }
        
        // $list = DB::table('visited_places')
        //         ->join('patients', 'patients.account_id', '=', 'visited_places.account_id')
        //         ->join('account_informations', 'account_informations.account_id', '=', 'patients.account_id')
        //         ->select('*')
        //         ->get();
        
        // if(!empty($list)){
        //     $jsonData = json_encode($list->toArray());
        //     dd($jsonData);
        // }
     
    }

    public function getHistory($username, $agent_id){
      // retrieve the history of visited places, temperature 
      // and transportation 
      // of the user by passing 
      // the username and id of the agent

      $agentExists = Account::where('id','=', $agent_id)->where('account_type','=', 'AGENCY')->get();
      
      $this->response['data'] = null;
      if(sizeof($agentExists) > 0){
        $userExists = Account::where('id', '=', $username)->where('account_type', '=','USER')->get();
        if(sizeof($userExists) > 0){
          $this->response['data']['visited_places'] = Visitedplace::where('account_id', '=', $userExists[0]['id'])->get();
          $this->response['data']['temperature_with_location'] = DB::table('temperatures')
            ->join('temperature_locations', 'temperatures.id','=', 'temperature_locations.temperature_id')
            ->select('temperatures.id', 'temperatures.account_id', 'temperatures.added_by', 'temperatures.value', 'temperatures.remarks', 'temperature_locations.longitude', 'temperature_locations.latitude', 'temperature_locations.route', 'temperature_locations.locality', 'temperature_locations.country', 'temperature_locations.region')
            ->where('account_id', '=', $userExists[0]['id'])
            ->get();
          $this->response['data']['rides'] = Ride::where('account_id', '=', $userExists[0]['id'])->get();
        }else{
          $this->response['error'] = 'User not found';
        }
      }else{
        $this->response['error'] = 'Agent not found';
      }
      return $this->response();
    }

    public function getStatus(Request $request){
      $data = $request->all();
      $status = 'negative';
      $this->response['data'] = array(
        'status' => $status
      );
      return $this->response();
    }
}
