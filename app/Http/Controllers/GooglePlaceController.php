<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GooglePlaceController extends APIController
{
  public function search(Request $request){
    $data = $request->all();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data['route']);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec ($ch);
    curl_close ($ch);

    $this->response['data'] = json_decode($response, true);
    return $this->response();
  }
}
