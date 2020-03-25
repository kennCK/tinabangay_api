<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
class Receipt extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $date;
    public $dataReceipt;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $data, $timezone)
    {
        $this->user = $user;
        $this->dataReceipt = $data;
        // echo json_encode($data);
        $this->date = Carbon::now()->copy()->tz($timezone)->format('F j, Y h:i A');
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return $this->subject('Receipt notification')->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))->view('email.receiptofficial');
    }
}
