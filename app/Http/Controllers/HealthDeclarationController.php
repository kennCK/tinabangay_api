<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HealthDeclaration;
use Carbon\Carbon;
class HealthDeclarationController extends APIController
{

  public $notificationClass = 'Increment\Common\Notification\Http\NotificationController';

  function __construct(){
    $this->model = new HealthDeclaration();
    $this->notRequired = array(
      'content'
    );
  }

  public function create(Request $request){
    $data = $request->all();
    $data['code'] = $this->generateCode();
    $this->model = new HealthDeclaration();
    $this->insertDB($data);
    if($this->response['data'] > 0){
      // send notification
      $notification = array(
        'from'          => $data['owner'],
        'to'            => $data['account_id'],
        'payload'       => 'form_request',
        'payload_value' => $this->response['data'],
        'route'         => '/form/'.$data['code'],
        'created_at'    => Carbon::now()
      );
      app($this->notificationClass)->createByParams($notification);
    }
    return $this->response();
  }

  public function generateCode(){
    $code = 'HDF-'.substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 60);
    $codeExist = HealthDeclaration::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }
}
