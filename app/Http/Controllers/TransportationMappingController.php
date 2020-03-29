<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transportation;
use App\Ride;
use App\Patient;
use Illuminate\Support\Facades\DB;

class TransportationMappingController extends Controller
{
    public function map(Request $request)
    {
        $data = $request->all();

        // $rides = Ride::all();
        // $ride = DB::table('transportations')
            // ->select('transportations.*')
        //     ->where('transportations.account_id', '=', )
        //     ->get();
        // error_log($ride);

        $positiveUser = DB::table('rides')
            ->join('patients', 'rides.account_id', '=', 'patients.account_id')
            ->where('patients.status', '=', $data['status'])
            ->whereNull('patients.deleted_at')
            ->whereNull('rides.deleted_at')
            ->select('rides.*')->get();

            $positiveUser = $positiveUser->groupBy('type');
            $array = array();
            foreach ($positiveUser as $key => $value) {
              $place = Ride::where('type', '=', $key)->first();
              $rides = Ride::where('type', '=', $key)->get();
              $pui = 0;
              $pum = 0;
              $positive = 0;
              $negative = 0;
              $death = 0;
                foreach ($rides as $keyrides) {
                  $patient = Patient::where('account_id', '=', $keyrides->account_id)->orderBy('created_at', 'desc')->first();
                  if($patient){
                    switch ($patient->status) {
                      case 'pui':
                        $pui++;
                        break;
                      case 'pum':
                        $pum++;
                        break;
                      case 'positive':
                          $positive++;
                          break; 
                      case 'death':
                        $death++;
                        break; 
                    }
                  }else{
                    $negative++;
                  }
                }
              $place['size'] = sizeof($rides);
              $place['positive_size'] = $positive;
              $place['pui_size'] = $pui;
              $place['pum_size'] = $pum;
              $place['negative_size'] = $negative;
              $place['death_size'] = $death;
            //   $place['transportation'] = $ride;
              $array[] = $place;
            }
            $keys = array_column($array, 'positive_size');
            array_multisort($keys, SORT_DESC, $array);
            $this->response['data'] = $array;
            return response()->json($this);
       
    }
}
