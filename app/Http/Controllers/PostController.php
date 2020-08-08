<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use Carbon\Carbon;
class PostController extends APIController
{
  function __construct(){
    $this->model = new Post();
  }

  public function create(Request $request){
    $data = $request->all();
    $data['code'] = $this->generateCode();

    $params = array(
      'code'        => $data['code'],
      'content'     => $data['content'],
      'created_at'  => Carbon::now()
    );

    $this->response['data'] = Post::insert($params);
    return $this->response();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    if(sizeof($this->response['data']) > 0){
      $i = 0;
      $result = $this->response['data'];
      foreach ($result as $key) {
        $this->response['data'][$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
        $i++;
      }
    }
    return $this->response();
  }

  public function generateCode(){
    $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 64);
    $codeExist = Post::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }
}
