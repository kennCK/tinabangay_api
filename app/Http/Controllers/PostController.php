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
