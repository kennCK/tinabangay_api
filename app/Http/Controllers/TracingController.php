<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VisitedPlace;
use App\Patient;
use App\UserInformation;
use DB;

class TracingController extends APIController
{
    public function tree(){
        // $patients=[];

        $placesList = VisitedPlace::with('patients','userInfo')->get();
        $userinFo = UserInformation::all();
        if(!empty($placesList)){
          $jsonData = json_encode($placesList->toArray());
          dd($jsonData);
        }
        
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
}
