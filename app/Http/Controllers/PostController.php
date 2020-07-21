<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
class PostController extends APIController
{
  function __construct(){
    $this->model = new Post();
  }
}
