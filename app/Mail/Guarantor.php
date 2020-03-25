<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
class Guarantor extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $date;
    public $email;
    public $code;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $email, $code, $timezone)
    {
        $this->user = $user;
        $this->email = $email;
        $this->details = $code;
        $this->date = Carbon::now()->copy()->tz($timezone)->format('F j, Y h:i A');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       return $this->subject('Guarantor notification')->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))->view('email.guarantor');
    }
}
