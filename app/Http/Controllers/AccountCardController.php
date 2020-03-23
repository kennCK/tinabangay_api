<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AccountCard;
use Carbon\Carbon;
class AccountCardController extends APIController
{
    function __construct(){
        $this->model = new AccountCard();
    }

    public function retrieve(Request $request){
      $data = $request->all();
      $result = AccountCard::where('account_id', '=', $data['account_id'])->orderBy('payload', 'asc')->get()->groupBy('payload');

      $keys = array();

      foreach ($result as $key) {
        $rowArray = [];
        foreach ($key as $row) {
          $rowArray[] = $row;
        }
        $temp = array(
          'title' => $key[0]['payload'],
          'content' => $rowArray
        );
        $keys[] = $temp;
      }
      return response()->json(array(
        'data' => $keys,
        'error' => null,
        'timestamps' => Carbon::now()
      ));
    }

    public function getByParams($column, $value, $type = null){
      $result = AccountCard::where($column, '=', $value)->orderBy('payload', 'asc')->get()->groupBy('payload');

      $keys = array();

      foreach ($result as $key) {
        $temp = array(
          'title' => $key[0]['payload'],
          'verified' => true
        );
        if($type != null && $type === 'ADMIN'){
          $temp['payload_value'] = $key[0]['payload_value'];
        }
        $keys[] = $temp;
      }

      return sizeof($keys) > 0 ? $keys : null;
    }
}
