<?php

namespace App\Http\Controllers;

use Mail;
use App\Mail\ResetPassword;
use App\Mail\Verification;
use App\Mail\ChangedPassword;
use App\Mail\Referral;
use App\Mail\LoginEmail;
use App\Mail\OtpEmail;
use App\Mail\NotifyReferrer;
use App\Mail\Receipt;
use App\Mail\NewMessage;
use App\Mail\Ledger;
use App\Mail\Deposit;
use Illuminate\Http\Request;

class EmailController extends APIController
{

    public $timezone = 'Asia/Manila';

    function __construct(){
        $this->localization();
        $this->timezone = $this->response['timezone'];
    }

    public function resetPassword($id){
    	$user = $this->retrieveAccountDetails($id);
    	if($user != null){
    		Mail::to($user['email'])->send(new ResetPassword($user, $this->response['timezone']));
    		return true;
    	}
    	return false;
    }

    public function verification($id){
        $user = $this->retrieveAccountDetails($id);
        if($user != null){
            Mail::to($user['email'])->send(new Verification($user, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function loginInvitation($id, $password){
    }

    public function changedPassword($id){
        $user = $this->retrieveAccountDetails($id);
        if($user != null){
            Mail::to($user['email'])->send(new ChangedPassword($user, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function loginEmail($id){
        $user = $this->retrieveAccountDetails($id);
        if($user != null){
            Mail::to($user['email'])->send(new LoginEmail($user, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function notifyReferrer($id){
        $user = $this->retrieveAccountDetails($id);
        if($user != null){
            Mail::to($user['email'])->send(new NotifyReferrer($user, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function otpEmail($id, $otpCode, $subject, $text){
        $user = $this->retrieveAccountDetails($id);
        if($user != null){
            Mail::to($user['email'])->send(new OtpEmail($subject, $user, $otpCode, $text, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function otpEmailFundTransfer($id, $otpCode){
        $user = $this->retrieveAccountDetails($id);
        $subject = 'OTP Notification';
        $text = "to continue for money transfer from your account.";
        if($user != null){
            Mail::to($user['email'])->send(new OtpEmail($subject, $user, $otpCode, $text, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function referral(Request $request){
        $data = $request->all();
        $user = $this->retrieveAccountDetails($data['account_id']);
        if($user != null){
            Mail::to($data['to_email'])->send(new Referral($user, $data['content'], $data['to_email'], $this->response['timezone']));
            $this->response['data'] = true;
        }
        return $this->response();
    }

    public function receipt($accountId, $data){
        $user = $this->retrieveAccountDetails($accountId);
        if($user != null && sizeof($data) > 0){
            Mail::to($user['email'])->send(new Receipt($user, $data[0], $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function ledger($accountId, $details, $subject){
        $user = $this->retrieveAccountDetails($accountId);
        if($user != null){
            Mail::to($user['email'])->send(new Ledger($user, $details, $subject, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function newMessage($accountId){
        $online = app('Increment\Account\Http\AccountOnlineController')->getStatus($accountId);
        $user = $this->retrieveAccountDetails($accountId);
        if($user != null && $online == false){
            Mail::to($user['email'])->send(new NewMessage($user, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function trial(Request $request){
        $data = $request->all();
        $user = $this->retrieveAccountDetails($data['account_id']);
        if($user != null){
            Mail::to($user['email'])->send(new LoginEmail($user, $this->response['timezone']));
            $this->response['data'] = true;
        }
        return $this->response();
    }


    public function investment($accountId, $details, $subject){
        $user = $this->retrieveAccountDetails($accountId);
        if($user != null){
            Mail::to($user['email'])->send(new Ledger($user, $details, $subject, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function deposit($accountId, $details, $subject){
        $this->localization();
        $user = $this->retrieveAccountDetails($accountId);
        if($user != null){
            Mail::to($user['email'])->send(new Deposit($user, $details, $subject, $this->response['timezone']));
            return true;
        }
        return false;
    }

    public function testSMS(Request $request){
        $this->sendSMS();
        return $this->response();
    }

    public function sendSMS(){
        $shortcode = env('SMS_SHORT_CODE');
        $passphrase = "143143@kennCK1994";
        $app_id = env('SMS_APP_ID');
        $app_secret = env('SMS_APP_SECRET');
        $address = "9171837855";
        $clientCorrelator = "264801";
        $message = "PHP SMS Test";
        echo $app_id;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://devapi.globelabs.com.ph/smsmessaging/v1/outbound/".$shortcode."/requests?app_id=".$app_id."&app_secret=".$app_secret."&passphrase=".$passphrase ,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\"outboundSMSMessageRequest\": { \"clientCorrelator\": \"".$clientCorrelator."\", \"senderAddress\": \"".$shortcode."\", \"outboundSMSTextMessage\": {\"message\": \"".$message."\"}, \"address\": \"".$address."\" } }",
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }
    }
}