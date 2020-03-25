<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QrCode;
class QrCodeController extends APIController
{
  function __construct(){
    $this->model = new QrCode();
  }

  public function generate(Request $request){
    /*
    $image = \QrCode::format('png')
                 ->merge('img/t.jpg', 0.1, true)
                 ->size(200)->errorCorrection('H')
                 ->generate('A simple example of QR code!');
$output_file = '/img/qr-code/img-' . time() . '.png';
Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
    */ 
    $data = $request->all();
    QRCode::text($data['code'])->png(); 
  }
}

