<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HealthDeclaration;
use Carbon\Carbon;
class HealthDeclarationController extends APIController
{

  public $notificationClass = 'Increment\Common\Notification\Http\NotificationController';
  public $merchantClass = 'Increment\Imarket\Merchant\Http\MerchantController';

  function __construct(){
    $this->model = new HealthDeclaration();
    $this->notRequired = array(
      'content'
    );
  }

  public function create(Request $request){
    $data = $request->all();
    $data['code'] = $this->generateCode();
    $updatedAt = null;

    if(isset($data['payload']) && ($data['payload'] == 'form_submitted/customer'|| $data['payload'] == 'form_submitted/employee_checkin' || $data['payload'] == 'form_submitted/employee_checkout')){
      $updatedAt = Carbon::now();
    }

    $params = array(
      'owner'       => $data['owner'],
      'account_id'  => $data['account_id'],
      'content'     => $data['content'],
      'code'        => $data['code'],
      'created_at'  => Carbon::now(),
      'updated_at'  => $updatedAt
    );

    $this->response['data'] = HealthDeclaration::insertGetId($params);

    if($this->response['data'] > 0){
      // send notification
      $notification = array(
        'from'          => $data['from'],
        'to'            => $data['to'],
        'payload'       => isset($data['payload']) ? $data['payload'] : 'form_request/customer',
        'payload_value' => $this->response['data'],
        'route'         => '/form/'.$data['code'],
        'created_at'    => Carbon::now()
      );
      app($this->notificationClass)->createByParams($notification);
    }

    $this->response['generated_code'] = $data['code'];
    return $this->response();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    if(sizeof($this->response['data']) > 0){
      $i = 0;
      $result = $this->response['data'];
      foreach ($result as $key) {
        $this->response['data'][$i]['merchant'] = app($this->merchantClass)->getByParams('account_id', $result[$i]['owner']);
        $this->response['data'][$i]['updated_at_human'] = null;
        if($result[$i]['updated_at'] != null){
          $this->response['data'][$i]['updated_at_human'] =Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['updated_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
        }
        $i++;
      }
    }
    return $this->response();
  }

  public function update(Request $request){
    $data = $request->all();

    $this->updateDB($data);
    if($this->response['data'] == true){
      $notification = array(
        'from'          => $data['from'],
        'to'            => $data['to'],
        'payload'       => isset($data['payload']) ? $data['payload'] : 'form_submitted/customer',
        'payload_value' => $data['id'],
        'route'         => '/form/'.$data['code'],
        'created_at'    => Carbon::now()
      );
      app($this->notificationClass)->createByParams($notification);
    }
    return $this->response();
  }

  public function generateCode(){
    // dont include '/' in str_shuffle
    $code = 'HDF-'.substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 60);
    $codeExist = HealthDeclaration::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }
}
